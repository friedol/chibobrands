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
        $types = DesignTaskType::with('department')->latest()->get();
        $departments = \App\Models\Department::orderBy('name')->get();
        return view('admin.design-task-types.index', compact('types', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = \App\Models\Department::orderBy('name')->get();
        return view('admin.design-task-types.create', compact('departments'));
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
            'department_id' => 'nullable|exists:departments,id',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('design-task-types', 'public');
        }

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
        $departments = \App\Models\Department::orderBy('name')->get();
        return view('admin.design-task-types.edit', compact('designTaskType', 'departments'));
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
            'department_id' => 'nullable|exists:departments,id',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('design-task-types', 'public');
        }

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
