<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('dashboard.user.index');
    }

    public function data()
    {
        $users = User::latest()->get()->map(fn($u) => [
            'id'          => $u->id,
            'employee_id' => $u->employee_id,
            'name'        => $u->name,
            'role'        => $u->role,
            'username'    => $u->username,
        ]);
        return response()->json(['data' => $users]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|unique:users',
            'name'        => 'required|string|max:255',
            'role'        => 'required|in:SPV,Admin',
            'username'    => 'required|string|unique:users',
            'password'    => 'required|string|min:6',
        ]);

        User::create([
            'employee_id'    => $request->employee_id,
            'name'           => $request->name,
            'role'           => $request->role,
            'username'       => $request->username,
            'password'       => $request->password,
            'reset_password' => true,
        ]);

        return response()->json(['message' => 'User berhasil ditambahkan.']);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'employee_id' => 'required|string|unique:users,employee_id,' . $user->id,
            'name'        => 'required|string|max:255',
            'role'        => 'required|in:SPV,Admin',
            'username'    => 'required|string|unique:users,username,' . $user->id,
        ]);

        $data = $request->only(['employee_id', 'name', 'role', 'username']);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $data['password']       = $request->password;
            $data['reset_password'] = true;
        }

        $user->update($data);

        return response()->json(['message' => 'User berhasil diupdate.']);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 422);
        }

        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
