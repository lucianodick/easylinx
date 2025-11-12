<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Bloqueia User-Agents suspeitos (bots maliciosos)
        $blockedUserAgents = [
            // 'curl', // Descomente para bloquear curl
            'wget',
            'python-requests',
            'Go-http-client',
            'bot',
            'crawler',
            'spider',
            'scraper',
        ];

        $userAgent = strtolower($request->userAgent() ?? '');
        
        foreach ($blockedUserAgents as $blocked) {
            if (str_contains($userAgent, strtolower($blocked))) {
                // Log tentativa bloqueada
                Log::warning('API access blocked', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                ]);

                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'User agent not allowed'
                ], 403);
            }
        }

        // 2. Valida formato do CNPJ (se fornecido)
        if ($request->has('cnpj')) {
            $cnpj = preg_replace('/[^0-9]/', '', $request->input('cnpj'));
            
            if (!empty($cnpj) && strlen($cnpj) !== 14) {
                Log::info('Invalid CNPJ format', [
                    'ip' => $request->ip(),
                    'cnpj' => $request->input('cnpj'),
                ]);

                return response()->json([
                    'error' => 'Invalid CNPJ format',
                    'message' => 'CNPJ deve conter 14 dígitos'
                ], 400);
            }
        }

        // 3. Adiciona headers de segurança na resposta
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
