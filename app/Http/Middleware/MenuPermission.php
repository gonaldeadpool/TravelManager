<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $user || ! $routeName || $user->isAdmin()) {
            return $next($request);
        }

        foreach ($this->menuRoutes() as $menu => $routes) {
            if ($this->matches($routeName, $routes) && ! $user->canAccessMenu($menu)) {
                abort(403);
            }
        }

        return $next($request);
    }

    private function matches(string $routeName, array $routes): bool
    {
        foreach ($routes as $route) {
            if (str_ends_with($route, '.*') && str_starts_with($routeName, rtrim($route, '*'))) {
                return true;
            }

            if ($routeName === $route) {
                return true;
            }
        }

        return false;
    }

    private function menuRoutes(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'clienti' => ['clienti', 'clienti.*'],
            'viaggi' => ['viaggi.*'],
            'calendario' => ['calendario', 'calendario.*'],
            'pratiche' => ['pratiche.*'],
            'amministrazione' => ['amministrazione', 'amministrazione.*'],
            'utenti' => ['utenti', 'utenti.*'],
        ];
    }
}
