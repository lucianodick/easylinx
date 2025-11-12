<?php

namespace App\Http\Controllers;

use App\Models\ApiRequestLog;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Estatísticas das últimas 24 horas
        $last24Hours = now()->subDay();
        
        $apiStats = [
            // Total de requisições nas últimas 24h
            'total_requests_24h' => ApiRequestLog::where('created_at', '>=', $last24Hours)->count(),
            
            // Taxa de cache hit
            'cache_hit_rate' => $this->getCacheHitRate($last24Hours),
            
            // Tempo médio de resposta
            'avg_response_time' => ApiRequestLog::where('created_at', '>=', $last24Hours)
                ->avg('response_time_ms'),
            
            // IPs mais ativos (top 5)
            'top_ips' => ApiRequestLog::select('ip_address', DB::raw('count(*) as total'))
                ->where('created_at', '>=', $last24Hours)
                ->groupBy('ip_address')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
            
            // Endpoints mais acessados
            'top_endpoints' => ApiRequestLog::select('endpoint', 'http_method', DB::raw('count(*) as total'))
                ->where('created_at', '>=', $last24Hours)
                ->groupBy('endpoint', 'http_method')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            
            // Sistemas mais consultados (se houver)
            'top_systems' => ApiRequestLog::select('system', DB::raw('count(*) as total'))
                ->where('created_at', '>=', $last24Hours)
                ->whereNotNull('system')
                ->groupBy('system')
                ->orderByDesc('total')
                ->get(),
            
            // Distribuição por status code
            'status_codes' => ApiRequestLog::select('status_code', DB::raw('count(*) as total'))
                ->where('created_at', '>=', $last24Hours)
                ->groupBy('status_code')
                ->orderByDesc('total')
                ->get(),
            
            // Total de 404s
            'total_404' => ApiRequestLog::where('created_at', '>=', $last24Hours)
                ->where('status_code', 404)
                ->count(),
            
            // Endpoints que retornaram 404 (top 10)
            'endpoints_404' => ApiRequestLog::select('endpoint', 'http_method', DB::raw('count(*) as total'))
                ->where('created_at', '>=', $last24Hours)
                ->where('status_code', 404)
                ->groupBy('endpoint', 'http_method')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            
            // Requisições por hora (últimas 24h)
            'requests_per_hour' => ApiRequestLog::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00') as hour"),
                DB::raw('count(*) as total')
            )
                ->where('created_at', '>=', $last24Hours)
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
        ];

        return Inertia::render('Dashboard', [
            'apiStats' => $apiStats,
        ]);
    }

    public function clearLogs()
    {
        ApiRequestLog::truncate();
        
        return back()->with('success', 'Logs da API limpos com sucesso!');
    }

    private function getCacheHitRate($since)
    {
        $total = ApiRequestLog::where('created_at', '>=', $since)->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $cacheHits = ApiRequestLog::where('created_at', '>=', $since)
            ->where('cache_hit', true)
            ->count();
        
        return round(($cacheHits / $total) * 100, 2);
    }
}
