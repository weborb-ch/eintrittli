<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Nightwatch\Facades\Nightwatch;
use Symfony\Component\HttpFoundation\Response;

class NightwatchIgnoreLivewire
{
    public function handle(Request $request, Closure $next): Response
    {
        if (preg_match('#^livewire-[^/]+/update#', $request->path())) {
            Nightwatch::dontSample();
        }

        return $next($request);
    }
}
