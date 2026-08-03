<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // batasi payload agar tidak log data besar
        $payload = $request->all();

        // jika payload terlalu besar, jangan log semuanya
        if (strlen(json_encode($payload)) > 5000) {
            $payload = 'Payload too large';
        }

        $responseContent = null;

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $responseContent = $response->getData(true);
        } elseif (method_exists($response, 'getContent')) {
            $responseContent = $response->getContent();
        }

        if (strlen(json_encode($responseContent)) > 5000) {
            $responseContent = 'Response too large';
        }


        // log info request
        Log::channel('api')->info('API Request', [
            'ip'      => $request->ip(),
            'method'  => $request->method(),
            'url'     => $request->fullUrl(),
            'payload' => $payload,
            'status'  => $response->status(),
            'response' => $responseContent,
        ]);

        return $response;
    }
}
