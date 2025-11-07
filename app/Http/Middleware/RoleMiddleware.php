<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // pastikan login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // role user & daftar role yg diizinkan (case-insensitive)
        $userRole = strtolower(auth()->user()->role ?? '');
        $roles    = array_map('strtolower', $roles);

        // tolak bila role tidak diizinkan
        if (!in_array($userRole, $roles, true)) {
            abort(403, 'Akses Ditolak.');
        }

        // CEO read-only (GET/HEAD saja)
        if ($userRole === 'ceo' && !in_array($request->method(), ['GET','HEAD'], true)) {
            abort(403, 'Read-only');
        }

        return $next($request);
    }
}
