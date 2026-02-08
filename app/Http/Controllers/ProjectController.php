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
        $projects = Project::latest()->get();
        return view('admin.project.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        Project::create($request->all());
        return back()->with('success', 'Project berhasil ditambahkan.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return back()->with('success', 'Project dihapus.');
    }
}