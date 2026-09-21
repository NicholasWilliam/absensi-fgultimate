<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Batasi akses route hanya untuk role tertentu.
     * Pemakaian di routes: ->middleware('role:admin') atau ->middleware('role:karyawan')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== $role) {
            abort(403, 'Akses ditolak. Halaman ini khusus untuk role: '.$role.'.');
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['username' => 'Akun kamu sudah dinonaktifkan.']);
        }

        return $next($request);
    }
}
