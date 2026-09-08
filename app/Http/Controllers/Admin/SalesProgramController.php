<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesProgram;
use Illuminate\Http\Request;

class SalesProgramController extends Controller
{
    public function index()
    {
        $programs = SalesProgram::orderBy('sort_order')->orderBy('name')->paginate(30);
        return view('admin.sales.programs.index', compact('programs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);
        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        SalesProgram::create($data);

        return redirect()->route('admin.sales.programs.index')
            ->with('success', 'Program "' . $data['name'] . '" added successfully.');
    }

    public function update(Request $request, SalesProgram $program)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);
        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $program->update($data);

        return redirect()->route('admin.sales.programs.index')
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(SalesProgram $program)
    {
        $program->delete();
        return redirect()->route('admin.sales.programs.index')
            ->with('success', 'Program deleted.');
    }
}
