<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function nextId()
    {
        return response()->json(['employee_id' => $this->generateNextEmployeeId()]);
    }

    private function generateNextEmployeeId(): string
    {
        $latest = User::where('employee_id', 'like', 'EMP%')
            ->orderByRaw('LENGTH(employee_id) DESC, employee_id DESC')
            ->value('employee_id');
        if (!$latest) return 'EMP001';
        $number = (int) substr($latest, 3);
        return 'EMP' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }

    public function checkUsername(Request $request)
    {
        $exists = User::where('username', $request->query('username'))
            ->when($request->query('exclude_id'), fn($q, $id) => $q->where('id', '!=', $id))
            ->exists();

        return response()->json(['available' => !$exists]);
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
            'name'     => 'required|string|max:255',
            'role'     => 'required|in:SPV,Admin',
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'employee_id'    => $this->generateNextEmployeeId(),
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
            'name'     => 'required|string|max:255',
            'role'     => 'required|in:SPV,Admin',
            'username' => 'required|string|unique:users,username,' . $user->id,
        ]);

        $user->update($request->only(['name', 'role', 'username']));

        return response()->json(['message' => 'User berhasil diupdate.']);
    }

    public function adminResetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->update([
            'password'       => $request->password,
            'reset_password' => false,
        ]);

        return response()->json(['message' => 'Password berhasil direset.']);
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 422);
        }

        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
