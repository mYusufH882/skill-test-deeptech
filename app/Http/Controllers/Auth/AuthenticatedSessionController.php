<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Enhanced redirect logic based on user type
        $user = Auth::user();

        // Debug: Log user type for troubleshooting
        \Log::info('User login:', [
            'email' => $user->email,
            'user_type' => $user->user_type,
            'isSuperAdmin' => $user->isSuperAdmin(),
            'isRegularAdmin' => $user->isRegularAdmin(),
            'isAdmin' => $user->isAdmin(),
            'isEmployee' => $user->isEmployee()
        ]);

        // Explicit role-based redirect
        if ($user->user_type === 'superadmin') {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->user_type === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->user_type === 'employee') {
            return redirect()->intended(route('employee.dashboard'));
        }

        // Fallback (shouldn't happen)
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
