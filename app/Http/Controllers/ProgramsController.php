<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;

class ProgramsController extends Controller
{
    public function index()
    {
        $programs = Program::orderBy('id', 'desc')->get();
        return view('programs.index', compact('programs'));
    }

    public function edit($id)
    {
        $program = Program::findOrFail($id);
        return view('programs.edit', compact('program'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'enrollment_fee' => 'required|numeric|min:0',
            'active' => 'required|boolean',
        ]);

        $program = Program::findOrFail($id);
        $program->name = $request->name;
        $program->description = $request->description;
        $program->enrollment_fee = $request->enrollment_fee;
        $program->active = $request->active;
        $program->save();

        return redirect()->route('programs.index')->with('success', 'Programa actualizado exitosamente.');
    }
}
