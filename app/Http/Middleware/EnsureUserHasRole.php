<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
  public function handle(Request $request, Closure $next, string ...$roles): Response
  {
    $user = $request->user();

    if (! $user) {
      abort(401);
    }

    $allowedRoles = collect($roles)
      ->map(fn(string $role) => UserRole::from($role))
      ->all();

    if (! in_array($user->role, $allowedRoles, true)) {
      abort(403);
    }

    return $next($request);
  }
}
