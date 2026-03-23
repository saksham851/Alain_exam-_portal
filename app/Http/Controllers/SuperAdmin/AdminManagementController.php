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
        $admins = User::whereIn('role', ['admin', 'manager'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('superadmin.admins.index', compact('admins'));
    }

    /**
     * Invite a new admin or manager by email
     */
    public function invite(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,manager',
        ], [
            'email.unique' => 'This email is already registered.',
            'email.required' => 'Email address is required.',
        ]);

        // Generate a random temp password
        $tempPassword = Str::random(12);

        // Create the user
        $user = User::create([
            'first_name'        => ucfirst($request->role),
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
                $role = ucfirst($user->role);
                $message->to($user->email)
                    ->subject("You have been invited as {$role} – Set Your Password");
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
        $admin->update(['status' => 0]);

        return back()->with('success', ucfirst($admin->role) . ' has been deactivated.');
    }

    /**
     * Re-activate staff
     */
    public function activate($id)
    {
        $admin = User::whereIn('role', ['admin', 'manager'])->findOrFail($id);
        $admin->update(['status' => 1]);

        return back()->with('success', ucfirst($admin->role) . ' has been activated.');
    }

    /**
     * Delete staff permanently
     */
    public function destroy($id)
    {
        $admin = User::whereIn('role', ['admin', 'manager'])->findOrFail($id);
        $admin->delete();

        return back()->with('success', ucfirst($admin->role) . ' has been deleted.');
    }

    /**
     * Resend invitation email
     */
    public function resendInvite($id)
    {
        $admin = User::whereIn('role', ['admin', 'manager'])->findOrFail($id);

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
                 $role = ucfirst($admin->role);
                $message->to($admin->email)
                    ->subject("{$role} Panel Access – Set Your Password");
            });

            return back()->with('success', "Invitation email resent to {$admin->email}.");
        } catch (\Exception $e) {
            return back()->with('error', "Could not send email: " . $e->getMessage());
        }
    }
}
