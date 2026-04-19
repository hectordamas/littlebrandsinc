<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User};

class UsersController extends Controller
{
    public function index(){
        $users = User::all();

        return view('users.index', [
            'users' => $users
        ]);
    }

    public function edit($id){
        $user = User::findOrFail($id);

        return view('users.edit', [
            'user' => $user
        ]);
    }
}
