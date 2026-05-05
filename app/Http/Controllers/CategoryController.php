<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for retail.
     */
    public function index(): View
    {
        $categories = Category::orderBy('name')
            ->withCount(['products' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('public.categories.index', compact('categories'));
    }

    /**
     * Display a listing of categories for wholesale.
     */
    public function wholesaleIndex(): View
    {
        $categories = Category::orderBy('name')
            ->withCount(['products' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('customer.categories.index', compact('categories'));
    }

    /**
     * Display the specified category for retail.
     */
    public function show(Category $category): View
    {
        // Use EnhancedProduct for retail with retail_base_price
        // Only show products that match this specific category
        $products = \App\Models\EnhancedProduct::query()
            ->where('retail_visible', true)
            ->where(function($query) use ($category) {
                // Try exact match first
                $query->where('category', $category->name)
                      // Then try case-insensitive match
                      ->orWhereRaw('LOWER(category) = ?', [strtolower($category->name)])
                      // Also try matching the slug
                      ->orWhere('category', $category->slug);
            })
            ->with(['images', 'variantCategories.items'])
            ->orderBy('name')
            ->paginate(12);

        $channel = 'retail';
        return view('public.categories.show', compact('category', 'products', 'channel'));
    }

    /**
     * Display the specified category for wholesale.
     */
    public function wholesaleShow(Category $category): View
    {
        // Use EnhancedProduct for wholesale with b2b_base_price
        // Match products by category name (same as retail)
        $products = \App\Models\EnhancedProduct::query()
            ->where(function($query) use ($category) {
                // Try exact match first
                $query->where('category', $category->name)
                      // Then try case-insensitive match
                      ->orWhereRaw('LOWER(category) = ?', [strtolower($category->name)])
                      // Also try matching the slug
                      ->orWhere('category', $category->slug);
            })
            ->with(['images', 'variantCategories.items'])
            ->orderBy('name')
            ->paginate(12);

        $channel = 'wholesale';
        return view('wholesale.categories.show', compact('category', 'products', 'channel'));
    }

    /**
     * Display a listing of categories for admin.
     */
    public function adminIndex(): View
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        // Comprehensive logging
        \Log::info('========== CATEGORY STORE REQUEST ==========');
        \Log::info('Request Method: ' . $request->method());
        \Log::info('Request URL: ' . $request->fullUrl());
        \Log::info('Request Data: ', $request->all());
        \Log::info('Has CSRF Token: ' . ($request->hasHeader('X-CSRF-TOKEN') || $request->has('_token') ? 'YES' : 'NO'));
        \Log::info('User: ' . (auth()->check() ? auth()->user()->name : 'Not authenticated'));
        
        try {
            \Log::info('Starting validation...');
            
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|max:2048',
                'is_active' => 'nullable|boolean',
            ]);

            \Log::info('Validation passed', $validated);

            $categoryData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active'),
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('categories', 'public');
                $categoryData['image_path'] = $path;
            }
            
            \Log::info('Category data prepared:', $categoryData);
            
            $category = Category::create($categoryData);

            \Log::info('Category created successfully!', [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ]);

            if ($request->wantsJson()) {
                return redirect()
                    ->route('admin.categories.index')
                    ->with('success', 'Category "' . $category->name . '" created successfully!');
            }

            \Log::info('Redirecting to categories index with success message');
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category "' . $category->name . '" created successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation failed:', $e->errors());
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            \Log::error('Error creating category', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create category: ' . $e->getMessage()])
                ->withInput();
        } finally {
            \Log::info('========== END CATEGORY STORE REQUEST ==========');
        }
    }

    /**
     * Display the specified category for admin.
     */
    public function adminShow(Category $category): View
    {
        // Load EnhancedProducts that match this category
        $products = \App\Models\EnhancedProduct::query()
            ->where(function($query) use ($category) {
                // Try exact match first
                $query->where('category', $category->name)
                      // Then try case-insensitive match
                      ->orWhereRaw('LOWER(category) = ?', [strtolower($category->name)])
                      // Also try matching the slug
                      ->orWhere('category', $category->slug);
            })
            ->with(['images'])
            ->orderBy('name')
            ->get();
        
        return view('admin.categories.show', compact('category', 'products'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $categoryData = [
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image_path) {
                \Storage::disk('public')->delete($category->image_path);
            }
            $path = $request->file('image')->store('categories', 'public');
            $categoryData['image_path'] = $path;
        }

        $category->update($categoryData);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Quickly toggle or set the active status for a category.
     */
    public function updateStatus(Request $request, Category $category): RedirectResponse
    {
        try {
        // Accept either explicit value or toggle
        $newStatus = $request->has('is_active')
            ? (bool) $request->boolean('is_active')
            : ! (bool) $category->is_active;

        $category->update(['is_active' => $newStatus]);

            $message = $newStatus 
                ? 'Category activated successfully.' 
                : 'Category deactivated successfully.';

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error updating category status: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update category status: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        try {
            // Check if category has EnhancedProducts (matching by category name)
            $productCount = \App\Models\EnhancedProduct::where(function($query) use ($category) {
                $query->where('category', $category->name)
                      ->orWhereRaw('LOWER(category) = ?', [strtolower($category->name)])
                      ->orWhere('category', $category->slug);
            })->count();

            if ($productCount > 0) {
                return redirect()->back()
                    ->with('error', "Cannot delete category. There are {$productCount} product(s) assigned to this category. Please reassign or delete the products first.");
            }

            // Also check old Product model for backwards compatibility
            if (method_exists($category, 'products') && $category->products()->count() > 0) {
            return redirect()->back()
                    ->with('error', 'Cannot delete category. There are products assigned to this category. Please reassign or delete the products first.');
        }

            // Delete category image if exists
            if ($category->image_path) {
                \Storage::disk('public')->delete($category->image_path);
            }

            $categoryName = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')
                ->with('success', "Category '{$categoryName}' deleted successfully!");
        } catch (\Exception $e) {
            \Log::error('Error deleting category: ' . $e->getMessage(), [
                'category_id' => $category->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }
}
