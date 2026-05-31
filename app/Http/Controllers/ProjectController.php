<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Constructor: Proteksi Route
     * Middleware ini memastikan hanya user dengan hak akses 'is-master-admin'
     * yang bisa menjalankan fungsi 'destroy' (Hapus Project).
     */
    public function __construct()
    {
        $this->middleware('can:is-master-admin')->only('destroy');
    }

    public function index()
    {
        $projects = Project::with('customer')->latest()->get();
        $customers = \App\Models\Customer::orderBy('name')->get();
        return view('admin.project.index', compact('projects', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'customer_id' => 'nullable|exists:customers,id',
        ]);
        Project::create($request->all());
        return back()->with('success', 'Project berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required',
            'customer_id' => 'nullable|exists:customers,id',
        ]);
        $project->update($request->all());
        return back()->with('success', 'Project berhasil diupdate.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return back()->with('success', 'Project dihapus.');
    }
}