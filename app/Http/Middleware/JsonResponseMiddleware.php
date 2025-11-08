<?php

namespace App\Http\Middleware;

use Closure;

class JsonResponseMiddleware
{
    public function handle($request, Closure $next)
    {

        $response = $next($request);
     if ($response->headers->get('Content-Type') === 'application/json') {

            $data = json_decode($response->getContent(), true);

            $jsonResponse = json_encode($data, JSON_NUMERIC_CHECK);


            $response->setContent($jsonResponse);
        }

        return $response;
    }
}
