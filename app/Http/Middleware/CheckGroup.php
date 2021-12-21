<?php

namespace App\Http\Middleware;

use Closure;

class CheckGroup
{
    /**
     * Verifica que el usuario pertenece a un grupo en especifico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $idGroup = $request->route('idGroup');

        $myGroups = auth()->user()->groups->pluck('id')->toArray();

        if (in_array($idGroup, $myGroups)){
            return $next($request);
        }

        return response()->json([
            'ready' => false,
            'message' => 'No está autorizado para acceder a este grupo',
        ], 403);
    }
}
