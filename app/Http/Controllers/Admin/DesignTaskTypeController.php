<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignTaskType;
use Illuminate\Http\Request;

class DesignTaskTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = DesignTaskType::latest()->get();
        return view('admin.design-task-types.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.design-task-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:design_task_types,name|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        DesignTaskType::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Task Type Created']);
        }

        return redirect()->route('admin.design-task-types.index')
            ->with('success', 'Design Task Type created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DesignTaskType $designTaskType)
    {
        return view('admin.design-task-types.edit', compact('designTaskType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DesignTaskType $designTaskType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:design_task_types,name,' . $designTaskType->id,
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $designTaskType->update($validated);

        return redirect()->route('admin.design-task-types.index')
            ->with('success', 'Design Task Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DesignTaskType $designTaskType)
    {
        $designTaskType->delete();

        return redirect()->route('admin.design-task-types.index')
            ->with('success', 'Design Task Type deleted successfully.');
    }
}
