<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        // Cek apakah user memiliki role
        if (!$user->role) {
            abort(403, 'Unauthorized - No role assigned');
        }

        // Cek apakah role user ada di dalam array roles yang diizinkan
        if (in_array($user->role->nama_role, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized - Insufficient permissions');
    }
}