<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDesign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CustomerDesignController extends Controller
{
    /**
     * Store a newly created design in storage.
     */
    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf,psd,ai,svg|max:20480', // 20MB max
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('customer_designs', 'public');

            CustomerDesign::create([
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'image_path' => $path,
                'title' => $request->title,
                'notes' => $request->notes,
            ]);

            return redirect()->back()->with('success', 'Design uploaded successfully!');
        }

        return redirect()->back()->with('error', 'Failed to upload image.');
    }

    /**
     * Remove the specified design from storage.
     */
    
    public function destroy(CustomerDesign $design)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin') {
            abort(403, 'Only admins can delete designs.');
        }

        // Delete from storage
        if (Storage::disk('public')->exists($design->image_path)) {
            Storage::disk('public')->delete($design->image_path);
        }

        $design->delete();

        return redirect()->back()->with('success', 'Design deleted successfully!');
    }
}
