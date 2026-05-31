<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        // KUNCI PERBAIKAN: paginate()
        $users = $query->latest()->paginate(10);

        return view('admin.pengguna.index', compact('users'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::all();
        return view('admin.pengguna.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'role' => 'required|in:master,service_admin,customer,viewer',
            'customer_id' => 'required_if:role,customer|nullable|exists:customers,id',
        ]);

        // SECURITY FIX: Create user without role first, then set role explicitly
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'customer_id' => $request->role === 'customer' ? $request->customer_id : null,
        ]);
        
        // Set role explicitly (not mass-assignable for security)
        $user->role = $request->role;
        $user->save();

        return redirect()->route('admin.pengguna.index')->with('success', 'User berhasil ditambah');
    }

    public function edit($id)
    {
        $pengguna = User::findOrFail($id);
        $customers = \App\Models\Customer::all();
        return view('admin.pengguna.edit', compact('pengguna', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|confirmed|min:6',
            'role' => 'required|in:master,service_admin,customer,viewer',
            'customer_id' => 'required_if:role,customer|nullable|exists:customers,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->customer_id = $request->role === 'customer' ? $request->customer_id : null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.pengguna.index')->with('success', 'User diperbarui');
    }

    public function destroy($id)
    {
        if (Auth::id() == $id)
            return back()->with('error', 'Tidak bisa hapus akun sendiri');
        User::findOrFail($id)->delete();
        return redirect()->route('admin.pengguna.index')->with('success', 'User dihapus');
    }
}