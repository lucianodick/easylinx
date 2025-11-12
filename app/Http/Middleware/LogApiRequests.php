<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Verifica se há cache para esta requisição (antes de processar)
        $cacheKey = $this->getCacheKey($request);
        $cacheHit = Cache::has($cacheKey);
        
        // Processa a requisição
        $response = $next($request);
        
        // Calcula tempo de resposta
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        // Registra o log apenas para rotas /api/*
        if (str_starts_with($request->path(), 'api/')) {
            $this->logRequest($request, $response, $responseTime, $cacheHit);
        }
        
        return $response;
    }
    
    private function logRequest(Request $request, Response $response, float $responseTime, bool $cacheHit): void
    {
        try {
            // Extrai dados específicos baseado no endpoint
            $system = $request->input('system');
            $cnpj = $request->input('cnpj');
            $machineName = $request->input('machine_name');
            
            // Remove formatação do CNPJ se houver
            if ($cnpj) {
                $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
            }
            
            // Normaliza machine_name
            if ($machineName) {
                $machineName = strtolower($machineName);
            }
            
            // Conta libraries retornadas (se houver no response)
            $librariesCount = 0;
            $responseData = json_decode($response->getContent(), true);
            if (isset($responseData['libraries']) && is_countable($responseData['libraries'])) {
                $librariesCount = count($responseData['libraries']);
            }
            
            ApiRequestLog::create([
                'ip_address' => $request->ip(),
                'endpoint' => '/' . $request->path(),
                'http_method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'request_params' => $request->all(),
                'system' => $system,
                'cnpj' => $cnpj,
                'machine_name' => $machineName,
                'cache_hit' => $cacheHit,
                'response_time_ms' => (int) $responseTime,
                'libraries_count' => $librariesCount,
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar log da API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    
    private function getCacheKey(Request $request): string
    {
        $cnpj = $request->input('cnpj');
        $machineName = $request->input('machine_name');
        $system = $request->input('system');
        
        if ($cnpj) {
            $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        }
        if ($machineName) {
            $machineName = strtolower($machineName);
        }
        
        return 'library_versions_' 
            . ($system ?? 'all_systems') . '_'
            . ($cnpj ?? 'default') . '_' 
            . ($machineName ?? 'no_machine');
    }
}
