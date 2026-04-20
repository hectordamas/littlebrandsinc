<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;

class BranchesController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'active' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $branch = new Branch();
        $branch->name = $request->name;
        $branch->active = $request->active;
        $branch->email = $request->email;
        $branch->phone = $request->phone;

        $branch->address = $request->address;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/logoBranches'), $filename);
            $branch->logo = 'uploads/logoBranches/' . $filename;
        } else {
            $branch->logo = null;
        }
        $branch->save();

        return redirect()->route('branches.index')->with('success', 'Sede creada exitosamente.');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'active' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $branch = Branch::findOrFail($id);
        $branch->name = $request->name;
        $branch->address = $request->address;
        $branch->active = $request->active;
        $branch->email = $request->email;
        $branch->phone = $request->phone;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/logoBranches'), $filename);
            $branch->logo = 'uploads/logoBranches/' . $filename;
        } else {
            $branch->logo = null;
        }
        $branch->save();

        return redirect()->route('branches.index')->with('success', 'Sede actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Sede eliminada exitosamente.');
    }
}
