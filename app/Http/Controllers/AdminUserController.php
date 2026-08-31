<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommercantRequest;
use App\Http\Requests\UpdateCommercantRequest;
use App\Models\User;

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

    public function store(StoreCommercantRequest $request)
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
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

    public function update(UpdateCommercantRequest $request, User $user)
    {
        if ($user->role !== 'commercant') {
            abort(403);
        }

        $user->update($request->validated());

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
