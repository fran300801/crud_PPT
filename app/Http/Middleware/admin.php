<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Consultar base de datos para saber si el usuario es admin
        $idUsuario = Auth::id();

        $rol = DB::table('users')->where('id', $idUsuario)->value('rol');

        if($rol !== 'admin') {
            return response()->json(['msg' => 'Acceso denegado.'], 403);
        }

        return $next($request);
    }
}
