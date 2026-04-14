<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ❌ Não está logado
        if (!auth()->check()) {
            return redirect('/');
        }

        // ❌ Não é admin
        if (auth()->user()->tipo !== 'admin') {
            return redirect('/dashboard-aluno')
                ->with('erro', 'Acesso negado!');
        }

        // ✅ É admin
        return $next($request);
    }
}