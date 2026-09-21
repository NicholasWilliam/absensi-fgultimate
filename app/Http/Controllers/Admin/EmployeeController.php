<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $karyawan = User::where('role', 'karyawan')->orderBy('name')->get();

        return view('admin.employees', compact('karyawan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => 'karyawan',
        ]);

        return back();
    }

    public function toggle(User $user)
    {
        abort_if($user->role !== 'karyawan', 403);

        $user->update(['is_active' => ! $user->is_active]);

        return back();
    }
}
