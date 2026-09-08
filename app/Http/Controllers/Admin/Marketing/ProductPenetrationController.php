<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\DesignTaskType;
use App\Models\EnhancedProduct;
use App\Models\ProductPenetration;
use Illuminate\Http\Request;

class ProductPenetrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = ProductPenetration::with(['product', 'taskType'])
            ->latest()
            ->get();

        return view('admin.marketing.product-penetration.index', compact('records'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = EnhancedProduct::orderBy('name')->get(['id', 'name', 'category', 'is_active']);
        $taskTypes = DesignTaskType::orderBy('name')->get(['id', 'name', 'department_id']);

        return view('admin.marketing.product-penetration.create', compact('products', 'taskTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_type'             => 'required|in:product,task_type',
            'product_id'            => 'required_if:item_type,product|nullable|integer',
            'task_type_id'          => 'required_if:item_type,task_type|nullable|integer',
            'target_segment'        => 'nullable|string|max:255',
            'current_penetration'   => 'nullable|numeric|min:0|max:100',
            'target_penetration'    => 'nullable|numeric|min:0|max:100',
            'strategy_notes'        => 'nullable|string',
            'status'                => 'required|in:planning,active,completed,on_hold',
        ]);

        ProductPenetration::create([
            'item_type'           => $request->item_type,
            'product_id'          => $request->item_type === 'product'    ? $request->product_id   : null,
            'task_type_id'        => $request->item_type === 'task_type'  ? $request->task_type_id : null,
            'target_segment'      => $request->target_segment,
            'current_penetration' => $request->current_penetration ?? 0,
            'target_penetration'  => $request->target_penetration  ?? 0,
            'strategy_notes'      => $request->strategy_notes,
            'status'              => $request->status,
            'created_by'          => auth()->id(),
        ]);

        return redirect()->route('admin.marketing.product-penetration.index')
            ->with('success', 'Product penetration activity saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductPenetration $productPenetration)
    {
        $productPenetration->delete();

        return redirect()->route('admin.marketing.product-penetration.index')
            ->with('success', 'Record deleted.');
    }
}
