<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Product;
use App\Models\Category;
use App\Models\HeroSlide;

class HomeController extends Controller
{
    /**
     * Display the retail homepage.
     */
    public function index(Request $request): View
    {
        $channel = 'retail';
        
        $query = \App\Models\EnhancedProduct::query()
            ->where('retail_visible', true)
            ->where('is_active', true)
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

        // Ordering
        $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 24);
        $products = $query->paginate($perPage);
        $products->appends($request->query());

        // Get categories for navigation
        $categories = \App\Models\Category::orderBy('name')->get();

        // Get hero slides for homepage
        $heroSlides = \App\Models\HeroSlide::active()->forHomepage()->ordered()->get();

        return view('public.pages.home', compact('products', 'categories', 'heroSlides', 'channel'));
    }

    /**
     * Display the wholesale homepage.
     */
    public function wholesaleIndex(): View
    {
        // Get wholesale products (all products for wholesale)
        $wholesaleProducts = Product::with(['images', 'category'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Get wholesale categories
        $categories = Category::orderBy('name')
            ->get();

        // Get hero slides for wholesale homepage
        $heroSlides = HeroSlide::active()->forPage('wholesale_homepage')->ordered()->get();

        return view('public.pages.wholesale-home', compact('wholesaleProducts', 'categories', 'heroSlides'));
    }

    /**
     * Display the about page.
     */
    public function about(): View
    {
        return view('public.pages.about');
    }

    /**
     * Display the contact page.
     */
    public function contact(): View
    {
        return view('public.pages.contact');
    }

    /**
     * Display the services page.
     */
    public function services(): View
    {
        $services = [
            [
                'title' => 'Business Cards',
                'description' => 'Professional business cards in various sizes and finishes',
                'icon' => 'fas fa-id-card',
                'features' => ['Premium cardstock', 'Multiple finishes', 'Fast turnaround']
            ],
            [
                'title' => 'Flyers & Brochures',
                'description' => 'Marketing materials to promote your business',
                'icon' => 'fas fa-file-alt',
                'features' => ['High-quality printing', 'Custom designs', 'Bulk discounts']
            ],
            [
                'title' => 'Banners & Signs',
                'description' => 'Large format printing for events and outdoor advertising',
                'icon' => 'fas fa-flag',
                'features' => ['Weather-resistant', 'Various sizes', 'Quick delivery']
            ],
            [
                'title' => 'Promotional Items',
                'description' => 'Custom branded merchandise and promotional products',
                'icon' => 'fas fa-gift',
                'features' => ['Custom branding', 'Wide selection', 'Competitive prices']
            ]
        ];

        // Get hero slides for services page
        $heroSlides = HeroSlide::active()->forServices()->ordered()->get();

        return view('public.pages.services', compact('services', 'heroSlides'));
    }

    /**
     * Display the privacy policy page.
     */
    public function privacy(): View
    {
        return view('public.pages.privacy');
    }

    /**
     * Display the terms of service page.
     */
    public function terms(): View
    {
        return view('public.pages.terms');
    }

    /**
     * Handle contact form submission.
     */
    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // Here you would typically send an email or save to database
        // For now, we'll just redirect with a success message

        return redirect()->route('contact')
            ->with('success', 'Thank you for your message! We will get back to you soon.');
    }

    /**
     * Display the Our Brand page (Retail).
     */
    public function ourBrand(): View
    {
        return view('public.pages.our-brand');
    }

    /**
     * Display the Our Brand page (Wholesale).
     */
    public function ourBrandWholesale(): View
    {
        return view('wholesale.pages.our-brand');
    }
}