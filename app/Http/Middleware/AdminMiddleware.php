<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->adalah_admin) {
            abort(403, 'Akses ditolak. Hak administrator diperlukan.');
        }

        return $next($request);
    }
}
