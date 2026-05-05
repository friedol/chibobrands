<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnhancedProductRequest;
use App\Models\EnhancedProduct;
use App\Models\ProductVariant;
use App\Models\EnhancedProductImage;
use App\Models\EnhancedProductPriceTier;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EnhancedProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // First, ensure all products have a sort_order
        $this->ensureSortOrderExists();
        
        $query = EnhancedProduct::with(['variants', 'images', 'priceTiers']);
        
        // Filter by stock status
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->where(function($q) {
                    $q->where('track_stock', false)
                      ->orWhere(function($q2) {
                          $q2->where('track_stock', true)
                             ->where('stock_quantity', '>', 0);
                      });
                });
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where(function($q) {
                    $q->where('track_stock', true)
                      ->where(function($q2) {
                          $q2->whereNull('stock_quantity')
                             ->orWhere('stock_quantity', '<=', 0);
                      });
                });
            }
        }
        
        $products = $query->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            

        // Load offers for each product
        $productBarcodes = $products->pluck('barcode');
        $offers = Offer::whereIn('product_barcode', $productBarcodes)
            ->where('is_active', true)
            ->get()
            ->groupBy('product_barcode');

        // Attach offers to each product
        $products->getCollection()->transform(function ($product) use ($offers) {
            $product->offers = $offers->get($product->barcode, collect());
            return $product;
        });

        // Preserve query parameters in pagination
        $products->appends($request->query());

        return view('admin.enhanced-products.index', compact('products'));
    }
    
    /**
     * Ensure all products have a sort_order value
     */
    private function ensureSortOrderExists()
    {
        $productsWithoutSortOrder = EnhancedProduct::whereNull('sort_order')
            ->orWhere('sort_order', 0)
            ->orderBy('created_at', 'asc')
            ->get();
            
        if ($productsWithoutSortOrder->count() > 0) {
            // Get the highest existing sort order
            $maxSortOrder = EnhancedProduct::whereNotNull('sort_order')
                ->where('sort_order', '>', 0)
                ->max('sort_order') ?? 0;
                
            // Assign sequential sort orders to products without them
            foreach ($productsWithoutSortOrder as $product) {
                $maxSortOrder++;
                $product->sort_order = $maxSortOrder;
                $product->save();
            }
        }
    }

    /**
     * Display the offers management page.
     */
    public function offers(Request $request, $product = null)
    {
        $products = EnhancedProduct::with(['variants', 'images', 'priceTiers'])
            ->orderBy('name')
            ->get();

        $selectedProduct = null;
        if ($product) {
            $selectedProduct = EnhancedProduct::where('barcode', $product)->first();
        }

        return view('admin.enhanced-products.offers', compact('products', 'selectedProduct'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        return view('admin.enhanced-products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEnhancedProductRequest $request)
    {
        // Minimal, robust create flow
        $data = $request->validated();

        // Always generate server-side identifiers (ignore client values) and ensure uniqueness
        $productId = EnhancedProduct::generateProductId();
        while (EnhancedProduct::where('product_id', $productId)->exists()) {
            $productId = EnhancedProduct::generateProductId();
        }

        $barcode = EnhancedProduct::generateBarcode();
        while (EnhancedProduct::where('barcode', $barcode)->exists()) {
            $barcode = EnhancedProduct::generateBarcode();
        }

        // Ensure booleans
        $data['retail_visible'] = $request->boolean('retail_visible');
        $data['wholesale_visible'] = $request->boolean('wholesale_visible');

        // Get the next sort order (count of all products + 1)
        $nextSortOrder = EnhancedProduct::count() + 1;

        // Persist product (without variants/tiers for now)
        $product = EnhancedProduct::create([
            'product_id' => $productId,
            'name' => $data['name'],
            'product_nickname' => $data['product_nickname'] ?? null,
            'slug' => $data['slug'] ?? null,
            'barcode' => $barcode,
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'brand' => $data['brand'] ?? null,
            'material' => $data['material'] ?? null,
            'weight' => $data['weight'] ?? null,
            'printing_type' => $data['printing_type'] ?? null,
            'buying_price' => $data['buying_price'],
            'retail_base_price' => $data['retail_base_price'] ?? null,
            'b2b_base_price' => $data['b2b_base_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'stock_unit' => $data['stock_unit'] ?? 'pcs',
            'min_quantity' => $data['min_quantity'] ?? 1,
            'max_quantity' => $data['max_quantity'] ?? null,
            'retail_visible' => $data['retail_visible'],
            'wholesale_visible' => $data['wholesale_visible'],
            'sort_order' => $nextSortOrder,
        ]);

        // Optional images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image && $image->isValid()) {
                    $path = $image->store('enhanced-products', 'public');
                    EnhancedProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                    ]);
                }
            }
        }

        // Process variant categories and items
        if ($request->has('variants')) {
            $this->processVariantData($product, $request->input('variants', []));
        }

        // Process price tiers
        if ($request->has('price_tiers')) {
            $this->processPriceTierData($product, $request->input('price_tiers', []));
        }

        return redirect()->route('admin.enhanced-products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($barcode)
    {
        $enhancedProduct = EnhancedProduct::where('barcode', $barcode)->firstOrFail();
        $enhancedProduct->load(['variants', 'images', 'priceTiers']);
        return view('admin.enhanced-products.show', compact('enhancedProduct'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($barcode)
    {
        $enhancedProduct = EnhancedProduct::where('barcode', $barcode)->firstOrFail();
        $enhancedProduct->load(['variantCategories.items', 'images', 'priceTiers']);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        return view('admin.enhanced-products.edit', compact('enhancedProduct', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreEnhancedProductRequest $request, $barcode)
    {
        try {
            // Increase execution time for this request
            set_time_limit(120);
            ini_set('memory_limit', '512M');
            
            Log::info('=== ENHANCED PRODUCT UPDATE METHOD CALLED ===');
            Log::info('Request method: ' . $request->method());
            Log::info('Request URL: ' . $request->url());
            Log::info('Request headers: ' . json_encode($request->headers->all()));
            Log::info('Starting product update', [
                'barcode' => $barcode,
                'name' => $request->name,
                'request_size' => strlen(json_encode($request->all()))
            ]);

            $enhancedProduct = EnhancedProduct::where('barcode', $barcode)->firstOrFail();
            Log::info('Product found', ['id' => $enhancedProduct->id, 'name' => $enhancedProduct->name]);

            // Update the product with basic fields only (simplified approach)
            Log::info('Starting basic product update...');
            $enhancedProduct->update([
                'name' => $request->name,
                'product_nickname' => $request->product_nickname,
                'slug' => $request->slug,
                'description' => $request->description,
                'category' => $request->category,
                'brand' => $request->brand,
                'material' => $request->material,
                'weight' => $request->weight,
                'printing_type' => $request->printing_type,
                'buying_price' => $request->buying_price,
                'retail_base_price' => $request->retail_base_price,
                'b2b_base_price' => $request->b2b_base_price,
                'stock_quantity' => $request->stock_quantity ?? 0,
                'stock_unit' => $request->stock_unit ?? 'pcs',
                'min_quantity' => $request->input('min_quantity', $enhancedProduct->min_quantity ?? 1),
                'max_quantity' => $request->input('max_quantity', $enhancedProduct->max_quantity),
                'retail_visible' => $request->boolean('retail_visible'),
                'wholesale_visible' => $request->boolean('wholesale_visible'),
            ]);

            Log::info('Basic product update completed successfully');
            
            // Handle image uploads
            if ($request->hasFile('images')) {
                Log::info('Processing image uploads...');
                foreach ($request->file('images') as $image) {
                    if ($image && $image->isValid()) {
                        $path = $image->store('enhanced-products', 'public');
                        EnhancedProductImage::create([
                            'product_id' => $enhancedProduct->id,
                            'image_path' => $path,
                        ]);
                        Log::info('Image uploaded: ' . $path);
                    }
                }
                Log::info('Image uploads completed');
            }

            // Process variant categories and items
            if ($request->has('variants')) {
                Log::info('Processing variant data...');
                $this->processVariantData($enhancedProduct, $request->input('variants', []));
                Log::info('Variant data processed');
            }

            // Process price tiers
            if ($request->has('price_tiers')) {
                Log::info('Processing price tier data...');
                $this->processPriceTierData($enhancedProduct, $request->input('price_tiers', []));
                Log::info('Price tier data processed');
            }
            
            Log::info('Product update completed successfully');
            return redirect()->route('admin.enhanced-products.show', $enhancedProduct->barcode)
                ->with('success', 'Product updated successfully!');

            // Fall-through try/catch is retained below
        } catch (\Exception $e) {
            Log::error('Product update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($barcode)
    {
        $enhancedProduct = EnhancedProduct::where('barcode', $barcode)->firstOrFail();
        
        try {
            // Delete associated images from storage
            foreach ($enhancedProduct->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete the product (cascade will handle related records)
            $enhancedProduct->delete();

            return redirect()->route('admin.enhanced-products.index')
                ->with('success', 'Product deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }

    /**
     * Show Adjust Quantity form.
     */
    public function editQuantity($barcode)
    {
        $enhancedProduct = EnhancedProduct::where('barcode', $barcode)->firstOrFail();
        return view('admin.enhanced-products.adjust-quantity', compact('enhancedProduct'));
    }

    /**
     * Apply quantity adjustment (increase/decrease).
     */
    public function updateQuantity(\Illuminate\Http\Request $request, $barcode)
    {
        $request->validate([
            'adjustment' => 'required|integer',
        ]);

        $enhancedProduct = EnhancedProduct::where('barcode', $barcode)->firstOrFail();
        $newQty = max(0, (int)$enhancedProduct->stock_quantity + (int)$request->input('adjustment'));
        $enhancedProduct->update(['stock_quantity' => $newQty]);

        return redirect()->route('admin.enhanced-products.edit', $enhancedProduct->barcode)
            ->with('success', 'Quantity updated successfully.');
    }

    /**
     * Delete a specific product image
     */
    public function deleteImage(EnhancedProductImage $image)
    {
        try {
            // Delete from storage
            Storage::disk('public')->delete($image->image_path);
            
            // Delete from database
            $image->delete();

            return response()->json(['success' => true, 'message' => 'Image deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete image: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate barcode for a product
     */
    public function generateBarcode(Request $request)
    {
        $barcode = EnhancedProduct::generateBarcode();
        return response()->json(['barcode' => $barcode]);
    }

    /**
     * Get price for specific quantity and customer type
     */
    public function getPrice(Request $request, $id)
    {
        $product = EnhancedProduct::with('priceTiers')->findOrFail($id);
        $quantity = (int) $request->query('quantity', 1);
        $customerType = $request->query('customer_type', 'retail');

        $unitPrice = $product->getPriceForQuantity($quantity, $customerType);

        if (!$unitPrice) {
            return response()->json([
                'error' => 'No price tier found for the specified quantity and customer type'
            ], 404);
        }

        return response()->json([
            'unit_price' => (float) $unitPrice,
            'formatted_unit_price' => number_format($unitPrice),
            'total' => (float) ($unitPrice * $quantity),
            'formatted_total' => number_format($unitPrice * $quantity),
            'quantity' => $quantity,
            'customer_type' => $customerType
        ]);
    }

    public function toggleVisibility(Request $request, $barcode)
    {
        $request->validate([
            'type' => 'required|in:retail,wholesale',
            'visible' => 'required|boolean'
        ]);

        $product = EnhancedProduct::where('barcode', $barcode)->firstOrFail();
        $type = $request->type;
        $visible = $request->visible;

        if ($type === 'retail') {
            $product->retail_visible = $visible;
        } else {
            $product->wholesale_visible = $visible;
        }

        $product->save();

        return response()->json(['success' => true]);
    }

    /**
     * Update sort order for multiple products
     */
    public function updateSortOrder(Request $request)
    {
        try {
            Log::info('Update sort order request received', [
                'all_data' => $request->all(),
                'sort_orders' => $request->sort_orders
            ]);
            
            $request->validate([
                'sort_orders' => 'required|array',
                'sort_orders.*.product_id' => 'required|integer|exists:enhanced_products,id',
                'sort_orders.*.sort_order' => 'required|integer|min:1|max:9999'
            ]);

            // Handle duplicate sort orders by auto-assigning sequential numbers
            $sortOrders = collect($request->sort_orders);
            $processedOrders = [];
            
            // Sort by the original sort_order to maintain relative order
            $sortedOrders = $sortOrders->sortBy('sort_order');
            
            // Assign sequential numbers starting from 1
            $sequentialNumber = 1;
            foreach ($sortedOrders as $sortData) {
                $processedOrders[] = [
                    'product_id' => $sortData['product_id'],
                    'sort_order' => $sequentialNumber
                ];
                $sequentialNumber++;
            }
            
            Log::info('Processed sort orders (duplicates resolved)', [
                'original' => $request->sort_orders,
                'processed' => $processedOrders
            ]);

            DB::transaction(function () use ($processedOrders) {
                foreach ($processedOrders as $sortData) {
                    Log::info('Updating product sort order', [
                        'product_id' => $sortData['product_id'],
                        'sort_order' => $sortData['sort_order']
                    ]);
                    
                    $updated = EnhancedProduct::where('id', $sortData['product_id'])
                        ->update(['sort_order' => $sortData['sort_order']]);
                        
                    Log::info('Update result', [
                        'product_id' => $sortData['product_id'],
                        'updated' => $updated
                    ]);
                }
            });

            Log::info('Sort order update completed successfully');
            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully',
                'updated_orders' => $processedOrders
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating sort order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update sort order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Fix sort order gaps and ensure sequential numbering
     */
    public function fixSortOrder()
    {
        try {
            // Get all products ordered by current sort_order, then by created_at
            $products = EnhancedProduct::orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Reassign sequential sort orders starting from 1
            foreach ($products as $index => $product) {
                $newSortOrder = $index + 1;
                if ($product->sort_order != $newSortOrder) {
                    $product->sort_order = $newSortOrder;
                    $product->save();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Sort order fixed successfully. All products now have sequential numbers starting from 1.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fix sort order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process variant data from form submission
     */
    private function processVariantData($product, $variantsData)
    {
        // Delete existing variant categories and items for this product
        $product->variantCategories()->each(function ($category) {
            $category->items()->delete();
            $category->delete();
        });

        // Process each variant category
        foreach ($variantsData as $categoryData) {
            if (empty($categoryData['category']) || empty($categoryData['options'])) {
                continue;
            }

            // Create variant category
            $variantCategory = \App\Models\ProductVariantCategory::create([
                'product_id' => $product->id,
                'category' => $categoryData['category'],
            ]);

            // Process each option in the category
            foreach ($categoryData['options'] as $optionData) {
                if (empty($optionData['name'])) {
                    continue;
                }

                // Create variant item
                \App\Models\ProductVariantItem::create([
                    'variant_category_id' => $variantCategory->id,
                    'name' => $optionData['name'],
                    'color_code' => $optionData['color_code'] ?? null,
                    'description' => $optionData['description'] ?? null,
                    'price' => 0, // Legacy field, keep for compatibility
                    'retail_price' => $optionData['retail_price'] ?? 0,
                    'wholesale_price' => $optionData['wholesale_price'] ?? 0,
                    'position' => 0,
                ]);
            }
        }
    }

    /**
     * Process price tier data from form submission
     */
    private function processPriceTierData($product, $priceTiersData)
    {
        // Delete existing price tiers for this product
        $product->priceTiers()->delete();

        // Process each price tier
        foreach ($priceTiersData as $tierData) {
            if (empty($tierData['customer_type']) || empty($tierData['min_quantity']) || empty($tierData['price_per_unit'])) {
                continue;
            }

            // Create price tier
            EnhancedProductPriceTier::create([
                'product_id' => $product->id,
                'customer_type' => $tierData['customer_type'],
                'min_quantity' => $tierData['min_quantity'],
                'max_quantity' => $tierData['max_quantity'] ?? null,
                'price_per_unit' => $tierData['price_per_unit'],
            ]);
        }
    }
}
