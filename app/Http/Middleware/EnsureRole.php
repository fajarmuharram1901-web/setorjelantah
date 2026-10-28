<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Daftarkan alias 'role' ini di bootstrap/app.php (Laravel 11) atau
     * app/Http/Kernel.php $middlewareAliases (Laravel 10 ke bawah):
     *   'role' => \App\Http\Middleware\EnsureRole::class,
     *
     * Pemakaian di route: ->middleware('role:admin')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || $request->user()->role !== $role) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
