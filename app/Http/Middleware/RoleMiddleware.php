<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Handle multiple roles separated by |
        $allowedRoles = explode('|', $role);

        // Check if user has any of the allowed roles
        $hasAccess = false;
        foreach ($allowedRoles as $allowedRole) {
            switch (trim($allowedRole)) {
                case 'superadmin':
                    if ($user->isSuperAdmin()) {
                        $hasAccess = true;
                    }
                    break;

                case 'admin':
                    if ($user->isAdmin()) {
                        $hasAccess = true;
                    }
                    break;

                case 'employee':
                    if ($user->isEmployee()) {
                        $hasAccess = true;
                    }
                    break;

                default:
                    if ($user->user_type === trim($allowedRole)) {
                        $hasAccess = true;
                    }
            }

            if ($hasAccess) break;
        }

        if (!$hasAccess) {
            abort(403, 'Access denied. Required role: ' . $role);
        }

        return $next($request);
    }
}
