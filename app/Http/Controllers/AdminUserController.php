<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommercantRequest;
use App\Http\Requests\UpdateCommercantRequest;
use App\Models\AdminActionLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

        $commercant = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => 'commercant',
            'suspended' => false,
        ]);

        AdminActionLog::record(Auth::user(), 'creation', $commercant);

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

        $avant = $user->only(['name', 'email']);
        $user->update($request->validated());

        $changements = array_diff_assoc($user->only(['name', 'email']), $avant);
        $details = $changements
            ? 'Modifié : '.implode(', ', array_map(fn ($champ, $val) => "{$champ} → {$val}", array_keys($changements), $changements))
            : null;

        AdminActionLog::record(Auth::user(), 'modification', $user, $details);

        return redirect()->route('admin.users.index')->with('success', 'Commerçant mis à jour.');
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'commercant') {
            abort(403);
        }

        AdminActionLog::record(Auth::user(), 'suppression', $user);

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Commerçant supprimé.');
    }

    public function suspend(User $user)
    {
        if ($user->role !== 'commercant') {
            abort(403);
        }

        $user->update(['suspended' => !$user->suspended]);

        AdminActionLog::record(Auth::user(), $user->suspended ? 'suspension' : 'reactivation', $user);

        return redirect()->route('admin.users.index')->with('success', 'Le statut du commerçant a été mis à jour.');
    }
}
