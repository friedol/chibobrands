<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerSource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerSourceController extends Controller
{
    /**
     * Display a listing of customer sources.
     */
    public function index(): View
    {
        $sources = CustomerSource::orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        return view('admin.settings.customer-sources', compact('sources'));
    }

    /**
     * Store a newly created customer source.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:customer_sources,name',
            'sort_order' => 'nullable|integer',
        ]);

        CustomerSource::create([
            'name' => $request->name,
            'is_active' => true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->back()->with('success', 'Customer source "' . $request->name . '" created successfully!');
    }

    /**
     * Update customer source status.
     */
    public function toggle(CustomerSource $source): RedirectResponse
    {
        $source->update([
            'is_active' => !$source->is_active,
        ]);

        return redirect()->back()->with('success', 'Customer source status updated!');
    }

    /**
     * Remove customer source.
     */
    public function destroy(CustomerSource $source): RedirectResponse
    {
        $source->delete();
        return redirect()->back()->with('success', 'Customer source deleted successfully!');
    }
}
