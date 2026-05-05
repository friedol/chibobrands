<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HeroSlideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = HeroSlide::ordered();
        
        // Filter by type (slides or ads)
        if ($request->has('type')) {
            if ($request->type === 'ads') {
                $query->ads();
            } elseif ($request->type === 'slides') {
                $query->slides();
            }
        }
        
        // Filter by page type
        if ($request->has('page_type') && $request->page_type !== '') {
            $query->forPage($request->page_type);
        }
        
        // Filter by ad type
        if ($request->has('ad_type') && $request->ad_type !== '') {
            $query->forAdType($request->ad_type);
        }
        
        // Sort by type first (slides before ads), then by page_type, then by sort_order
        $slides = $query->get()->sortBy([
            ['is_ad', 'asc'], // Sort slides first, then ads
            ['page_type', 'asc'],
            ['sort_order', 'asc']
        ])->values();
        
        return view('admin.hero-slides.index', compact('slides'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $isAd = $request->get('type') === 'ad';
        return view('admin.hero-slides.create', compact('isAd'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg,mp4,avi,mov,wmv,flv,webm,mkv,3gp,mpg,mpeg|max:10240',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'button_color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'page_type' => 'required|string|in:all,homepage,services,products,wholesale_services,wholesale_homepage',
            'is_ad' => 'boolean',
            'ad_type' => 'nullable|string|in:banner,popup,sidebar,inline',
            'ad_position' => 'nullable|string|in:top,bottom,left,right,center',
            'ad_duration' => 'nullable|integer|min:1',
            'ad_closable' => 'boolean',
            'ad_target_audience' => 'nullable|string|in:all,retail,wholesale,new_customers,returning_customers',
            'ad_start_date' => 'nullable|date',
            'ad_end_date' => 'nullable|date|after_or_equal:ad_start_date',
            'ad_budget' => 'nullable|numeric|min:0',
            'ad_cost_per_click' => 'nullable|numeric|min:0',
            'ad_cost_per_impression' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        // `hero_slides.title` is non-nullable in DB, so never allow NULL.
        $data['title'] = (string) ($request->input('title') ?? '');
        
        // Handle image/video upload - use stream to prevent memory exhaustion
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imagePath = $file->store('hero-slides', 'public');
            $data['image_path'] = $imagePath;
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_ad'] = $request->input('is_ad', '0') == '1';
        $data['ad_closable'] = $request->has('ad_closable');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        HeroSlide::create($data);

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero slide created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.show', compact('heroSlide'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HeroSlide $heroSlide)
    {
        $isAd = $heroSlide->is_ad;
        return view('admin.hero-slides.edit', compact('heroSlide', 'isAd'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HeroSlide $heroSlide)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg,mp4,avi,mov,wmv,flv,webm,mkv,3gp,mpg,mpeg|max:10240',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'button_color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'page_type' => 'required|string|in:all,homepage,services,products,wholesale_services,wholesale_homepage',
            'is_ad' => 'boolean',
            'ad_type' => 'nullable|string|in:banner,popup,sidebar,inline',
            'ad_position' => 'nullable|string|in:top,bottom,left,right,center',
            'ad_duration' => 'nullable|integer|min:1',
            'ad_closable' => 'boolean',
            'ad_target_audience' => 'nullable|string|in:all,retail,wholesale,new_customers,returning_customers',
            'ad_start_date' => 'nullable|date',
            'ad_end_date' => 'nullable|date|after_or_equal:ad_start_date',
            'ad_budget' => 'nullable|numeric|min:0',
            'ad_cost_per_click' => 'nullable|numeric|min:0',
            'ad_cost_per_impression' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        // Ensure DB constraint is satisfied even when admin leaves title blank.
        $data['title'] = (string) ($request->input('title') ?? '');
        
        // Handle image/video upload - use stream to prevent memory exhaustion
        if ($request->hasFile('image')) {
            // Delete old image/video
            if ($heroSlide->image_path) {
                Storage::disk('public')->delete($heroSlide->image_path);
            }
            
            $file = $request->file('image');
            $imagePath = $file->store('hero-slides', 'public');
            $data['image_path'] = $imagePath;
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_ad'] = $request->input('is_ad', '0') == '1';
        $data['ad_closable'] = $request->has('ad_closable');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $heroSlide->update($data);

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero slide updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HeroSlide $heroSlide)
    {
        // Delete image file
        if ($heroSlide->image_path) {
            Storage::disk('public')->delete($heroSlide->image_path);
        }

        $heroSlide->delete();

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero slide deleted successfully!');
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'slides' => 'required|array',
            'slides.*.id' => 'required|exists:hero_slides,id',
            'slides.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->slides as $slide) {
            HeroSlide::where('id', $slide['id'])
                ->update(['sort_order' => $slide['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Track ad click
     */
    public function trackClick(HeroSlide $heroSlide)
    {
        if ($heroSlide->is_ad) {
            $heroSlide->incrementClickCount();
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Track ad impression
     */
    public function trackImpression(HeroSlide $heroSlide)
    {
        if ($heroSlide->is_ad) {
            $heroSlide->incrementImpressionCount();
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Get ad analytics
     */
    public function analytics()
    {
        $ads = HeroSlide::ads()->get();
        $analytics = [
            'total_ads' => $ads->count(),
            'active_ads' => $ads->where('is_active', true)->count(),
            'total_clicks' => $ads->sum('ad_click_count'),
            'total_impressions' => $ads->sum('ad_impression_count'),
            'total_budget' => $ads->sum('ad_budget'),
            'ads_by_type' => $ads->groupBy('ad_type')->map->count(),
            'ads_by_audience' => $ads->groupBy('ad_target_audience')->map->count(),
        ];
        
        return view('admin.hero-slides.analytics', compact('ads', 'analytics'));
    }
}
