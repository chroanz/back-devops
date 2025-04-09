<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * @var User $user
         */
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'msg' => 'Usuário não identificado'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'msg' => 'Usuário não tem permissão'], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
