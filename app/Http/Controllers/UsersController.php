<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\{User};

class UsersController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        return view('users.profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $request->validate([
            'dial_code' => 'nullable|string|max:6',
            'whatsapp' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->dial_code = $request->dial_code;
        $user->whatsapp = $request->whatsapp;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.profile')->with('success', 'Perfil actualizado exitosamente.');
    }

    public function index(){
        $users = User::orderBy('id', 'desc')->get();

        return view('users.index', [
            'users' => $users
        ]);
    }

    public function parents()
    {
        $parents = User::where('role', 'Padre')->orderBy('id', 'desc')->get();

        return view('parents.index', [
            'parents' => $parents,
        ]);
    }

    public function trainers()
    {
        $trainers = User::where('role', 'Coach')->orderBy('id', 'desc')->get();

        return view('trainers.index', [
            'trainers' => $trainers,
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->dial_code = $request->dial_code;
        $user->whatsapp = $request->whatsapp;
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }   

    public function edit($id){
        $user = User::findOrFail($id);

        return view('users.edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request, $id){
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'whatsapp' => 'nullable|string|max:20',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->dial_code = $request->dial_code;
        $user->whatsapp = $request->whatsapp;

        if($request->password){
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy($id){
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
