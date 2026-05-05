<?php

namespace App\Http\Controllers;

use App\Models\EnhancedProduct;
use App\Models\HeroSlide;
use Illuminate\Http\Request;

class ProductDisplayController extends Controller
{
    public function indexRetail(Request $request)
    {
        $query = EnhancedProduct::query()
            ->where('retail_visible', true)
            ->with(['images', 'variantCategories.items']);

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

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by price range
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

        $categories = \App\Models\Category::query()->orderBy('name')->get();
        $heroSlides = \App\Models\HeroSlide::active()->forHomepage()->ordered()->get();
        $channel = 'retail';
        return view('public.products.index', compact('products','categories','channel','heroSlides'));
    }

    public function indexWholesale(Request $request)
    {
        $query = EnhancedProduct::query()
            ->where('wholesale_visible', true)
            ->with(['images', 'variantCategories.items', 'priceTiers' => function($q){ $q->where('customer_type','wholesale'); }]);

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

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by price range
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

        $categories = \App\Models\Category::query()->orderBy('name')->get();
        $channel = 'wholesale';
        return view('public.products.index', compact('products','categories','channel'));
    }
    /**
     * Display the specified product for frontend customers
     */
    public function show($barcode)
    {
        $product = EnhancedProduct::with(['images', 'variants', 'variantCategories.items', 'priceTiers'])
            ->where('barcode', $barcode)
            ->firstOrFail();

        // Determine channel based on the current domain/route
        $channel = $this->getCurrentChannel();
        $customerType = $channel; // Use channel as customer type

        // Filter tiers based on customer type
        $priceTiers = $product->priceTiers()
            ->where('customer_type', $customerType)
            ->orderBy('min_quantity')
            ->get();

        // Check visibility based on customer type
        if ($customerType === 'retail' && !$product->retail_visible) {
            abort(404, 'Product not available for retail customers');
        }

        if ($customerType === 'wholesale' && !$product->wholesale_visible) {
            abort(404, 'Product not available for wholesale customers');
        }

        return view('public.products.show', compact('product', 'priceTiers', 'customerType', 'channel'));
    }

    /**
     * Determine the current channel based on the request
     */
    private function getCurrentChannel()
    {
        // Check if we're on the wholesale domain or route
        if (request()->is('b2b/*') || request()->getHost() === 'b2b.127.0.0.1:8000') {
            return 'wholesale';
        }
        
        // Default to retail
        return 'retail';
    }

    /**
     * Get price for specific quantity and customer type (AJAX endpoint)
     */
    public function getPrice(Request $request, $barcode)
    {
        $product = EnhancedProduct::with('priceTiers')
            ->where('barcode', $barcode)
            ->firstOrFail();

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

    /**
     * Autocomplete for wholesale products (first-letter and more).
     */
    public function autocompleteWholesale(Request $request)
    {
        $term = (string) $request->query('q', '');
        $results = [];

        if ($term !== '') {
            $results = EnhancedProduct::query()
                ->where('wholesale_visible', true)
                ->where('is_active', true)
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%'.$term.'%')
                      ->orWhere('barcode', 'like', '%'.$term.'%')
                      ->orWhere('category', 'like', '%'.$term.'%')
                      ->orWhere('product_nickname', 'like', '%'.$term.'%');
                })
                ->with(['images'])
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->map(function ($p) {
                    $img = optional($p->images->first());
                    $defaultUrl = asset('images/default.webp');
                    $imageUrl = $defaultUrl;
                    if ($img && !empty($img->image_path)) {
                        $candidate = public_path('storage/'.ltrim($img->image_path, '/'));
                        $imageUrl = file_exists($candidate) ? '/storage/' . ltrim($img->image_path, '/') : '/images/default.webp';
                    }
                    
                    // Clean display name
                    $displayName = preg_replace('/\s+\d+$/', '', (string) $p->name);
                    
                    return [
                        'name' => $displayName,
                        'barcode' => $p->barcode,
                        'category' => $p->category,
                        'image' => $imageUrl,
                        'price' => (float) ($p->b2b_base_price ?? $p->buying_price ?? 0),
                        'url' => !empty($p->barcode) ? route('wholesale.product.show', $p->barcode) : '#',
                    ];
                });
        }

        return response()->json(['items' => $results]);
    }

    /**
     * Autocomplete for retail products (first-letter and more).
     */
    public function autocompleteRetail(Request $request)
    {
        $term = (string) $request->query('q', '');
        $results = [];

        if ($term !== '') {
            $results = EnhancedProduct::query()
                ->where('retail_visible', true)
                ->where('is_active', true)
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%'.$term.'%')
                      ->orWhere('barcode', 'like', '%'.$term.'%')
                      ->orWhere('category', 'like', '%'.$term.'%')
                      ->orWhere('product_nickname', 'like', '%'.$term.'%');
                })
                ->with(['images'])
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->map(function ($p) {
                    $img = optional($p->images->first());
                    $defaultUrl = asset('images/default.webp');
                    $imageUrl = $defaultUrl;
                    if ($img && !empty($img->image_path)) {
                        $candidate = public_path('storage/'.ltrim($img->image_path, '/'));
                        $imageUrl = file_exists($candidate) ? '/storage/' . ltrim($img->image_path, '/') : '/images/default.webp';
                    }
                    
                    // Clean display name
                    $displayName = preg_replace('/\s+\d+$/', '', (string) $p->name);
                    
                    return [
                        'name' => $displayName,
                        'barcode' => $p->barcode,
                        'category' => $p->category,
                        'image' => $imageUrl,
                        'price' => (float) ($p->retail_base_price ?? $p->buying_price ?? 0),
                        'url' => !empty($p->barcode) ? route('product.show', $p->barcode) : '#',
                    ];
                });
        }

        return response()->json(['items' => $results]);
    }
}
