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
        return view('admin.pengguna.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required|confirmed|min:6']);
        User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'role' => 'admin']);
        return redirect()->route('admin.pengguna.index')->with('success', 'User berhasil ditambah');
    }

    public function edit($id)
    {
        $pengguna = User::findOrFail($id);
        return view('admin.pengguna.edit', compact('pengguna'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate(['name' => 'required', 'email' => 'required|email|unique:users,email,' . $id, 'password' => 'nullable|confirmed|min:6']);
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password'))
            $user->password = Hash::make($request->password);
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