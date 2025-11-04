<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsSimple
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin', '*');

        // ✅ Responde pré-flight (OPTIONS)
        if ($request->getMethod() === 'OPTIONS') {
            return response()->noContent(204)
                ->withHeaders([
                    'Access-Control-Allow-Origin'      => $origin,
                    'Vary'                             => 'Origin',
                    'Access-Control-Allow-Credentials' => 'true',
                    'Access-Control-Allow-Methods'     => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                    'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, X-Requested-With, Accept, Origin',
                    'Access-Control-Max-Age'           => '86400',
                ]);
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        // ✅ Aplica headers CORS em todas as respostas normais também
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Vary', 'Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With, Accept, Origin');

        return $response;
    }
}
