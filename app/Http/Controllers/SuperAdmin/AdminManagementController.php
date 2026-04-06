<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminManagementController extends Controller
{
    /**
     * Show list of all admins and managers
     */
    public function index()
    {
        $query = User::whereIn('role', ['admin', 'manager']);
        
        // Admins can only see and manage Managers
        if (auth()->user()->role === 'admin') {
            $query->where('role', 'manager');
        }

        // Search functionalitiy (Matches Student Management)
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('superadmin.admins.index', compact('admins'));
    }

    /**
     * Invite a new admin or manager by email
     */
    public function invite(Request $request)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,manager',
        ];

        // Admins can only invite Managers
        if (auth()->user()->role === 'admin') {
            $request->merge(['role' => 'manager']);
        }

        $request->validate($rules, [
            'email.unique' => 'This email is already registered.',
            'email.required' => 'Email address is required.',
        ]);

        // Generate a random temp password that meets complexity requirements
        $tempPassword = $this->generateStrongPassword(12);

        // Create the user
        $user = User::create([
            'first_name'        => $request->first_name,
            'last_name'         => '',
            'email'             => $request->email,
            'password'          => Hash::make($tempPassword),
            'role'              => $request->role,
            'status'            => 1,
            'is_blocked'        => false,
            'email_verified_at' => now(),
        ]);

        // Generate password reset token
        $token = Password::createToken($user);
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        // Send welcome email with set-password link
        try {
            Mail::send('emails.admin_invite', [
                'user'          => $user,
                'tempPassword'  => $tempPassword,
                'resetUrl'      => $resetUrl,
                'roleName'      => ucfirst($request->role)
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject("Mentara Health Credentials");
            });
        } catch (\Exception $e) {
            return redirect()->route('superadmin.admins.index')
                ->with('warning', ucfirst($request->role) . " created but email could not be sent. Error: " . $e->getMessage());
        }

        return redirect()->route('superadmin.admins.index')
            ->with('success', ucfirst($request->role) . " invitation sent to {$request->email}. They can now set their password via email.");
    }

    /**
     * Remove (deactivate) staff
     */
    public function deactivate($id)
    {
        $admin = User::whereIn('role', ['admin', 'manager'])->findOrFail($id);
        
        // Security: Admin can't deactivate another admin
        if (auth()->user()->role === 'admin' && $admin->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $admin->update(['status' => 0]);

        return back()->with('success', ucfirst($admin->role) . ' has been deactivated.');
    }

    /**
     * Re-activate staff
     */
    public function activate($id)
    {
        $admin = User::whereIn('role', ['admin', 'manager'])->findOrFail($id);

        // Security: Admin can't activate another admin
        if (auth()->user()->role === 'admin' && $admin->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $admin->update(['status' => 1]);

        return back()->with('success', ucfirst($admin->role) . ' has been activated.');
    }

    /**
     * Delete staff permanently
     */
    public function destroy($id)
    {
        $admin = User::whereIn('role', ['admin', 'manager'])->findOrFail($id);

        // Security: Admin can't delete another admin
        if (auth()->user()->role === 'admin' && $admin->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $admin->delete();

        return back()->with('success', ucfirst($admin->role) . ' has been deleted.');
    }

    /**
     * Resend invitation email
     */
    public function resendInvite($id)
    {
        $admin = User::whereIn('role', ['admin', 'manager'])->findOrFail($id);

        // Security: Admin can't resend invite to another admin
        if (auth()->user()->role === 'admin' && $admin->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $token = Password::createToken($admin);
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $admin->email,
        ], false));

        try {
            Mail::send('emails.admin_invite', [
                'user'         => $admin,
                'tempPassword' => null,
                'resetUrl'     => $resetUrl,
                'roleName'     => ucfirst($admin->role)
            ], function ($message) use ($admin) {
                $message->to($admin->email)
                    ->subject("Mentara Health Credentials");
            });

            return back()->with('success', "Invitation email resent to {$admin->email}.");
        } catch (\Exception $e) {
            return back()->with('error', "Could not send email: " . $e->getMessage());
        }
    }
    /**
     * Generate a strong random password with at least:
     * 1 Uppercase, 1 Number, 1 Special Character
     */
    private function generateStrongPassword($length = 12)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers   = '0123456789';
        $special   = '!@#$%^&*()-_+';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';

        // Ensure at least one of each required type
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill the rest with random characters from all sets
        $all = $uppercase . $numbers . $special . $lowercase;
        for ($i = 0; $i < ($length - 3); $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle the results to avoid predictable positions
        return str_shuffle($password);
    }
}
