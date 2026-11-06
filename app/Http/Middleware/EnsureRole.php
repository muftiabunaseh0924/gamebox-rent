<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route($role === 'admin' ? 'admin.login' : 'login');
        }

        // Akun yang dinonaktifkan di tengah sesi langsung dikeluarkan.
        if (! $user->isActive()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route($role === 'admin' ? 'admin.login' : 'login')
                ->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi admin.']);
        }

        if ($user->role !== $role) {
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}