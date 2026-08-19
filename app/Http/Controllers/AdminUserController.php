<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $commercants = User::where('role', 'commercant')->paginate(10);
        return view('admin.users.index', compact('commercants'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'commercant',
            'suspended' => false,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Commerçant ajouté avec succès.');
    }

    public function edit(User $user)
    {
        if ($user->role !== 'commercant') {
            abort(403);
        }
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'commercant') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Commerçant mis à jour.');
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'commercant') {
            abort(403);
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Commerçant supprimé.');
    }

    public function suspend(User $user)
    {
        if ($user->role !== 'commercant') {
            abort(403);
        }

        $user->update(['suspended' => !$user->suspended]);

        return redirect()->route('admin.users.index')->with('success', 'Le statut du commerçant a été mis à jour.');
    }
}
