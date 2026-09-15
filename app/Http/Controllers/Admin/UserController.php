<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['branch', 'role'])->paginate(15);
        return view('admin.pengguna.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $roles = Role::all();

        return view('admin.pengguna.create', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'branch_id' => 'nullable|exists:branches,id',
            'role_id'   => 'required|exists:roles,id',
            'phone'     => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'branch_id' => $validated['branch_id'] ?: null,
            'role_id'   => $validated['role_id'],
            'phone'     => $validated['phone'] ?? null,
            'status'    => ($request->has('is_active') && !$request->boolean('is_active')) ? 'inactive' : 'active',
        ]);

        AuditLog::log(auth()->user(), 'user.create', "Menambahkan pengguna baru: {$user->name} ({$user->email})");

        return redirect()->route('admin.pengguna.index')->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $pengguna)
    {
        $branches = Branch::where('is_active', true)->get();
        $roles = Role::all();
        $pengguna->load(['branch', 'role']);

        return view('admin.pengguna.edit', [
            'user'     => $pengguna,
            'branches' => $branches,
            'roles'    => $roles,
        ]);
    }

    public function update(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $pengguna->id,
            'password'  => 'nullable|string|min:6',
            'branch_id' => 'nullable|exists:branches,id',
            'role_id'   => 'required|exists:roles,id',
            'phone'     => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $payload = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'branch_id' => $validated['branch_id'] ?: null,
            'role_id'   => $validated['role_id'],
            'phone'     => $validated['phone'] ?? null,
            'status'    => ($request->has('is_active') && !$request->boolean('is_active')) ? 'inactive' : 'active',
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $pengguna->update($payload);

        AuditLog::log(auth()->user(), 'user.update', "Memperbarui pengguna: {$pengguna->name}");

        return redirect()->route('admin.pengguna.index')->with('success', "Pengguna {$pengguna->name} berhasil diperbarui.");
    }

    public function destroy(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $pengguna->name;
        $pengguna->delete();

        AuditLog::log(auth()->user(), 'user.delete', "Menghapus pengguna: {$name}");

        return redirect()->route('admin.pengguna.index')->with('success', "Pengguna {$name} berhasil dihapus.");
    }
}
