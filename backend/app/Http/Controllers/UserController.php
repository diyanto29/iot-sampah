<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);
        $adminCount = User::where('role', 'admin')->count();
        $petugasCount = User::where('role', 'petugas')->count();

        return view('users.index', compact('users', 'adminCount', 'petugasCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('users.index')->with('success', "Akun {$validated['name']} ({$validated['role']}) berhasil dibuat!");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => "required|email|unique:users,email,{$id}",
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,petugas',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', "Data akun {$user->name} berhasil diperbarui!");
    }

    public function destroy($id)
    {
        if (Auth::id() == $id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "Akun {$name} berhasil dihapus.");
    }
}

