<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariation;
use App\Models\ProductAddon;
use App\Models\ProductCustomInput;
use App\Models\ProductImage;
use App\Models\Offer;
use App\Models\HeroSlide;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ImageService;

class ProductController extends Controller
{
    /**
     * Display a listing of products for retail website.
     */
    public function index(Request $request): View
    {
        // Use EnhancedProduct for retail with retail_base_price
        $query = \App\Models\EnhancedProduct::query()
            ->where('retail_visible', true)
            ->with(['images', 'variantCategories.items']);

        // Filter by category (EnhancedProduct stores category as string)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by price range (use retail_base_price)
        if ($request->filled('min_price')) {
            $query->where('retail_base_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('retail_base_price', '<=', $request->max_price);
        }

        // Always use admin-defined sort order first, then apply additional sorting
        $query->orderBy('sort_order', 'asc');
        
        // Additional sorting functionality (only if not using admin sort order)
        $sortBy = $request->get('sort', 'admin_order');
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('retail_base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('retail_base_price', 'desc');
                break;
            case 'category':
                $query->orderBy('category', 'asc')->orderBy('name', 'asc');
                break;
            case 'stock_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'latest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'admin_order':
            default:
                // Already ordered by sort_order, add created_at as secondary sort
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination with customizable per page
        $perPage = $request->get('per_page', 24);
        $perPage = in_array($perPage, [12, 24, 48, 96]) ? $perPage : 24;
        
        $products = $query->paginate($perPage);
        $products->appends($request->query());

        // Load offers for each product
        $productBarcodes = $products->pluck('barcode');
        $offers = Offer::whereIn('product_barcode', $productBarcodes)
            ->where('is_active', true)
            ->get()
            ->groupBy('product_barcode');

        // Attach offers to each product
        $products->each(function ($product) use ($offers) {
            $product->offers = $offers->get($product->barcode, collect());
        });

        $categories = Category::orderBy('name')->get();
        $heroSlides = HeroSlide::active()->forProducts()->ordered()->get();

        $channel = 'retail';
        return view('public.products.index', compact('products', 'categories', 'channel', 'heroSlides'));
    }

    /**
     * Display a listing of products for wholesale website.
     */
    public function wholesaleIndex(Request $request): View
    {
        // Use EnhancedProduct for wholesale website and only show wholesale-visible items
        $query = \App\Models\EnhancedProduct::query()
            ->where('wholesale_visible', true)
            ->with([
                'images',
                'variantCategories.items',
                'priceTiers' => function ($q) { $q->where('customer_type', 'wholesale')->orderBy('min_quantity'); }
            ]);

        // Optional category filter if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by price range (use b2b_base_price)
        if ($request->filled('min_price')) {
            $query->where('b2b_base_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('b2b_base_price', '<=', $request->max_price);
        }

        // Always use admin-defined sort order first, then apply additional sorting
        $query->orderBy('sort_order', 'asc');
        
        // Additional sorting functionality (only if not using admin sort order)
        $sortBy = $request->get('sort', 'admin_order');
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('b2b_base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('b2b_base_price', 'desc');
                break;
            case 'category':
                $query->orderBy('category', 'asc')->orderBy('name', 'asc');
                break;
            case 'stock_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'latest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'admin_order':
            default:
                // Already ordered by sort_order, add created_at as secondary sort
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination with customizable per page
        $perPage = $request->get('per_page', 24);
        $perPage = in_array($perPage, [12, 24, 48, 96]) ? $perPage : 24;
        
        $products = $query->paginate($perPage);
        $products->appends($request->query());

        // Load offers for each product
        $productBarcodes = $products->pluck('barcode');
        $offers = \App\Models\Offer::whereIn('product_barcode', $productBarcodes)
            ->where('is_active', true)
            ->get()
            ->groupBy('product_barcode');

        // Attach offers to each product
        $products->each(function ($product) use ($offers) {
            $product->offers = $offers->get($product->barcode, collect());
        });

        $categories = Category::orderBy('name')->get();
        $heroSlides = HeroSlide::active()->forProducts()->ordered()->get();

        $channel = 'wholesale';
        return view('public.products.index', compact('products', 'categories', 'channel', 'heroSlides'));
    }

    /**
     * Display the specified product for retail.
     */
    public function show(Product $product): View
    {
        // Load all relationships with safe ordering (no is_primary column)
        $product->load([
            'category', 
            'images' => function($query) {
                $query->orderBy('id');
            }, 
            'variations' => function($query) {
                $query->orderBy('name')->orderBy('option_value');
            }, 
            'addons' => function($query) {
                $query->orderBy('addon_name');
            }, 
            'customInputs' => function($query) {
                $query->orderBy('input_label');
            },
            'priceTiers' => function($query) {
                $query->orderBy('min_quantity');
            }
        ]);
        
        // Get related products from same category
        $relatedProducts = Product::with(['images', 'category'])
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->limit(4)
            ->get();

        // If no related products in same category, get from other categories
        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::with(['images', 'category'])
                ->where('status', 'active')
                ->where('id', '!=', $product->id)
                ->limit(4)
                ->get();
        }

        return view('public.products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Search products for retail.
     */
    public function search(Request $request): View
    {
        $query = Product::with(['category', 'images', 'variations', 'addons'])
            ->where('status', 'active')
            ->where('is_wholesale', false);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('material', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        $products = $query->orderBy('name')
            ->orderBy('name')
            ->paginate(12);

        $categories = Category::orderBy('name')
            ->orderBy('name')
            ->get();

        return view('public.products.search', compact('products', 'categories'));
    }

    /**
     * Display the specified product for wholesale.
     */
    public function wholesaleShow(Product $product): \Illuminate\Http\RedirectResponse
    {
        // Redirect legacy ID-based wholesale URL to canonical barcode URL when available
        if (!empty($product->barcode)) {
            return redirect()->route('wholesale.product.show', $product->barcode);
        }
        // Fallback to wholesale landing if no barcode exists
        return redirect('/b2b/shop');
    }

    /**
     * Search products for wholesale.
     */
    public function wholesaleSearch(Request $request): View
    {
        $query = Product::with(['category', 'images', 'variations', 'addons'])
            ->where('status', 'active')
            ->where('is_wholesale', true);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('material', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        $products = $query->orderBy('name')
            ->orderBy('name')
            ->paginate(12);

        $categories = Category::orderBy('name')
            ->orderBy('name')
            ->get();

        return view('customer.products.search', compact('products', 'categories'));
    }

    /**
     * Display a listing of products for admin.
     */
    public function adminIndex(Request $request): View
    {
        $query = Product::with(['category', 'images']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('status', 'active');
                    break;
                case 'inactive':
                    $query->where('status', 'inactive');
                    break;
                case 'wholesale':
                    $query->where('is_wholesale', true);
                    break;
            }
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = Category::orderBy('name')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'availability' => 'required|in:in_stock,out_of_stock,custom',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
        ]);

        // Create product
        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'sku' => $request->sku,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'category_id' => $request->category_id,
            'availability' => $request->availability,
            'weight' => $request->weight,
            'dimensions' => $request->dimensions,
            'features' => $request->features,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'status' => $request->has('is_active') ? 'active' : 'inactive',
            'is_wholesale' => $request->has('is_wholesale'),
            'customization_allowed' => $request->has('customization_allowed'),
            'track_stock' => $request->has('track_stock'),
            'stock_quantity' => $request->stock_quantity ?? 0,
            'low_stock_threshold' => $request->low_stock_threshold ?? 10,
            'min_quantity' => $request->min_quantity ?? 1,
            'max_quantity' => $request->max_quantity ?? 100,
        ]);

        // Handle image uploads with WebP conversion
        if ($request->hasFile('images')) {
            $imageService = new ImageService();
            
            foreach ($request->file('images') as $index => $image) {
                // Validate image
                $errors = $imageService->validateImage($image);
                if (!empty($errors)) {
                    return back()->withErrors(['images' => $errors])->withInput();
                }
                
                // Process and upload image
                $result = $imageService->uploadAndProcess($image, 'products');
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $result['original'],
                    'webp_path' => $result['webp'],
                    'is_primary' => $index === 0,
                ]);
            }
        }

        // Handle variations
        if ($request->has('variations')) {
            foreach ($request->variations as $variation) {
                if (!empty($variation['name']) && !empty($variation['option_value'])) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'name' => $variation['name'],
                        'option_value' => $variation['option_value'],
                        'extra_price' => $variation['extra_price'] ?? 0,
                    ]);
                }
            }
        }

        // Handle add-ons
        if ($request->has('addons')) {
            foreach ($request->addons as $addon) {
                if (!empty($addon['addon_name'])) {
                    ProductAddon::create([
                        'product_id' => $product->id,
                        'addon_name' => $addon['addon_name'],
                        'addon_price' => $addon['addon_price'] ?? 0,
                        'description' => $addon['description'] ?? null,
                    ]);
                }
            }
        }

        // Handle custom inputs
        if ($request->has('custom_inputs')) {
            foreach ($request->custom_inputs as $input) {
                if (!empty($input['input_label'])) {
                    ProductCustomInput::create([
                        'product_id' => $product->id,
                        'input_label' => $input['input_label'],
                        'input_type' => $input['input_type'] ?? 'text',
                        'is_required' => isset($input['is_required']),
                        'placeholder' => $input['placeholder'] ?? null,
                    ]);
                }
            }
        }

        // Handle pricing tiers
        $tierInputs = $request->input('tiers', []);
        foreach($tierInputs as $row) {
            if (empty($row['min']) || empty($row['price'])) continue;
            $product->priceTiers()->create([
                'min_quantity' => (int) $row['min'],
                'max_quantity' => $row['max'] ? (int) $row['max'] : null,
                'price_per_unit' => (float) $row['price'],
                'is_wholesale' => isset($row['is_wholesale']) ? true : false,
            ]);
        }

        // Update product attributes
        $product->update([
            'brand' => $request->brand,
            'color_options' => $request->color_options ? json_decode($request->color_options) : null,
            'size_options' => $request->size_options ? json_decode($request->size_options) : null,
            'printing_type' => $request->printing_type,
            'specifications' => $request->specifications ? json_decode($request->specifications) : null,
        ]);

        // Update price_range_display automatically
        $min = $product->priceTiers()->min('price_per_unit');
        $max = $product->priceTiers()->max('price_per_unit');
        if($min && $max){
            $product->price_range_display = number_format($min) . ' - ' . number_format($max);
            $product->save();
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product for admin.
     */
    public function adminShow(Product $product): View
    {
        $product->load(['category', 'images', 'variations', 'addons', 'customInputs', 'priceTiers']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load(['images', 'variations', 'addons', 'customInputs', 'priceTiers']);
        $categories = Category::orderBy('name')->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'availability' => 'required|in:in_stock,out_of_stock,custom',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
        ]);

        // Update product
        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'sku' => $request->sku,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'category_id' => $request->category_id,
            'availability' => $request->availability,
            'weight' => $request->weight,
            'dimensions' => $request->dimensions,
            'features' => $request->features,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'status' => $request->has('is_active') ? 'active' : 'inactive',
            'is_wholesale' => $request->has('is_wholesale'),
            'customization_allowed' => $request->has('customization_allowed'),
            'track_stock' => $request->has('track_stock'),
            'stock_quantity' => $request->stock_quantity ?? 0,
            'low_stock_threshold' => $request->low_stock_threshold ?? 10,
            'min_quantity' => $request->min_quantity ?? 1,
            'max_quantity' => $request->max_quantity ?? 100,
        ]);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'is_primary' => false, // Don't make new images primary by default
                ]);
            }
        }

        // Handle pricing tiers - delete existing and recreate for simplicity
        $tierInputs = $request->input('tiers', []);
        $product->priceTiers()->delete();
        foreach($tierInputs as $row) {
            if (empty($row['min']) || empty($row['price'])) continue;
            $product->priceTiers()->create([
                'min_quantity' => (int) $row['min'],
                'max_quantity' => $row['max'] ? (int) $row['max'] : null,
                'price_per_unit' => (float) $row['price'],
                'is_wholesale' => isset($row['is_wholesale']) ? true : false,
            ]);
        }

        // Update product attributes
        $product->update([
            'brand' => $request->brand,
            'color_options' => $request->color_options ? json_decode($request->color_options) : null,
            'size_options' => $request->size_options ? json_decode($request->size_options) : null,
            'printing_type' => $request->printing_type,
            'specifications' => $request->specifications ? json_decode($request->specifications) : null,
        ]);

        // Update price_range_display automatically
        $min = $product->priceTiers()->min('price_per_unit');
        $max = $product->priceTiers()->max('price_per_unit');
        if($min && $max){
            $product->price_range_display = number_format($min) . ' - ' . number_format($max);
            $product->save();
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Delete associated images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        // Delete the product (cascade will handle related records)
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }



    /**
     * Display products by category for retail.
     */
    public function category(Category $category): View
    {
        $products = Product::where('category_id', $category->id)
            ->where('status', 'active')
            ->paginate(12);

        return view('public.products.category', compact('products', 'category'));
    }

    /**
     * Display products by category for wholesale.
     */
    public function wholesaleCategory(Category $category): View
    {
        $products = Product::where('category_id', $category->id)
            ->where('status', 'active')
            ->where('is_wholesale', true)
            ->paginate(12);

        return view('customer.products.category', compact('products', 'category'));
    }

    /**
     * Get price for a product based on quantity and site mode.
     */
    public function getPrice($id, Request $request)
    {
        $quantity = (int) $request->query('quantity', 1);
        $siteMode = config('site.mode','retail'); // ensure DetectSiteMode middleware sets this
        $product = Product::with('priceTiers')->findOrFail($id);

        // choose tier collection according to site mode
        $tiers = $product->priceTiers;
        if ($siteMode === 'wholesale') {
            $tiers = $product->wholesaleTiers;
        } else {
            $tiers = $product->retailTiers;
        }

        // find matching tier
        $tier = $tiers->where('min_quantity', '<=', $quantity)
                      ->filter(function($t) use ($quantity) {
                         return is_null($t->max_quantity) ? true : ($t->max_quantity >= $quantity);
                      })->sortBy('min_quantity')->last();

        $unitPrice = $tier ? $tier->price_per_unit : ($siteMode === 'wholesale' ? $product->wholesale_price : $product->base_price);

        return response()->json([
            'unit_price' => (float) $unitPrice,
            'formatted_unit_price' => number_format($unitPrice),
            'total' => (float) ($unitPrice * $quantity),
            'formatted_total' => number_format($unitPrice * $quantity),
        ]);
    }
}
