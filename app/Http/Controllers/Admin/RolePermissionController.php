<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.dashboard');
    }

    public function create() { return redirect()->route('admin.dashboard'); }
    public function store(Request $request) { return redirect()->route('admin.dashboard'); }
    public function show($id) { return redirect()->route('admin.dashboard'); }
    public function edit($id) { return redirect()->route('admin.dashboard'); }
    public function update(Request $request, $id) { return redirect()->route('admin.dashboard'); }
    public function destroy($id) { return redirect()->route('admin.dashboard'); }
}
