<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Only show students, not admins
        $users = User::where('role', 'student')
            ->where('status', 1)
            ->with(['studentExams.attempts']) // Eager load attempts (correct relationship name)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $user = null;
        return view('admin.users.edit', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'student', // Always create as student
            'status' => 1,
            'is_blocked' => false,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Student created successfully!');
    }

    public function edit($id)
    {
        $user = User::where('role', 'student')->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::where('role', 'student')->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'phone' => 'nullable|string|max:20',
        ]);

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Student updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::where('role', 'student')->findOrFail($id);
        $user->update(['status' => 0]); // Soft delete
        
        return back()->with('success', 'Student deleted successfully!');
    }
}
