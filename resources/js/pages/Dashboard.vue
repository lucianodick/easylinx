<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Activity, TrendingUp, Clock, Server, Globe, CheckCircle, XCircle, AlertTriangle, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface ApiStats {
    total_requests_24h: number;
    cache_hit_rate: number;
    avg_response_time: number;
    top_ips: Array<{ ip_address: string; total: number }>;
    top_endpoints: Array<{ endpoint: string; http_method: string; total: number }>;
    top_systems: Array<{ system: string; total: number }>;
    status_codes: Array<{ status_code: number; total: number }>;
    total_404: number;
    endpoints_404: Array<{ endpoint: string; http_method: string; total: number }>;
    requests_per_hour: Array<{ hour: string; total: number }>;
}

const props = defineProps<{
    apiStats: ApiStats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const avgResponseTime = computed(() => {
    return props.apiStats.avg_response_time 
        ? Math.round(props.apiStats.avg_response_time) 
        : 0;
});

const clearLogs = () => {
    if (confirm('Tem certeza que deseja limpar todos os logs da API? Esta ação não pode ser desfeita.')) {
        router.delete('/dashboard/clear-logs', {
            preserveScroll: true,
            onSuccess: () => {
                alert('Logs limpos com sucesso!');
            },
        });
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground">
                        Análise de uso da API nas últimas 24 horas
                    </p>
                </div>
                
                <Button 
                    variant="destructive" 
                    @click="clearLogs"
                    class="gap-2"
                >
                    <Trash2 class="h-4 w-4" />
                    Limpar Logs
                </Button>
            </div>

            <!-- Cards Principais -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <!-- Total de Requisições -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Total de Requisições
                        </CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ apiStats.total_requests_24h.toLocaleString() }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Últimas 24 horas
                        </p>
                    </CardContent>
                </Card>

                <!-- Taxa de Cache Hit -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Taxa de Cache
                        </CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ apiStats.cache_hit_rate }}%
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Cache hit rate
                        </p>
                    </CardContent>
                </Card>

                <!-- Tempo Médio de Resposta -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Tempo Médio
                        </CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ avgResponseTime }}ms
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Tempo de resposta
                        </p>
                    </CardContent>
                </Card>

                <!-- Erros 404 -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Erros 404
                        </CardTitle>
                        <AlertTriangle class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold" :class="apiStats.total_404 > 0 ? 'text-orange-500' : ''">
                            {{ apiStats.total_404 }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Não encontrados
                        </p>
                    </CardContent>
                </Card>

                <!-- Sistemas Ativos -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Sistemas Ativos
                        </CardTitle>
                        <Server class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ apiStats.top_systems.length }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Sistemas diferentes
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Tabelas de Dados -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Top Endpoints -->
                <Card>
                    <CardHeader>
                        <CardTitle>Endpoints Mais Acessados</CardTitle>
                        <CardDescription>APIs mais utilizadas</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="endpoint in apiStats.top_endpoints"
                                :key="`${endpoint.http_method}-${endpoint.endpoint}`"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2">
                                    <Badge variant="outline" class="font-mono text-xs">
                                        {{ endpoint.http_method }}
                                    </Badge>
                                    <span class="text-sm">{{ endpoint.endpoint }}</span>
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ endpoint.total }}
                                </div>
                            </div>
                            <div
                                v-if="apiStats.top_endpoints.length === 0"
                                class="py-4 text-center text-sm text-muted-foreground"
                            >
                                Nenhuma requisição registrada
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Status Codes -->
                <Card>
                    <CardHeader>
                        <CardTitle>Status HTTP</CardTitle>
                        <CardDescription>Distribuição de respostas</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="status in apiStats.status_codes"
                                :key="status.status_code"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2">
                                    <CheckCircle v-if="status.status_code < 400" class="h-4 w-4 text-green-500" />
                                    <XCircle v-else class="h-4 w-4 text-red-500" />
                                    <span class="font-mono font-semibold">{{ status.status_code }}</span>
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ status.total }} requisições
                                </div>
                            </div>
                            <div
                                v-if="apiStats.status_codes.length === 0"
                                class="py-4 text-center text-sm text-muted-foreground"
                            >
                                Nenhuma requisição registrada
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Segunda linha de tabelas -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Top IPs -->
                <Card>
                    <CardHeader>
                        <CardTitle>IPs Mais Ativos</CardTitle>
                        <CardDescription>Top 5 endereços IP</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="ip in apiStats.top_ips"
                                :key="ip.ip_address"
                                class="flex items-center justify-between"
                            >
                                <div class="font-mono text-sm">{{ ip.ip_address }}</div>
                                <div class="flex items-center gap-2">
                                    <div class="text-sm text-muted-foreground">
                                        {{ ip.total }} requisições
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="apiStats.top_ips.length === 0"
                                class="py-4 text-center text-sm text-muted-foreground"
                            >
                                Nenhuma requisição registrada
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top Sistemas -->
                <Card>
                    <CardHeader>
                        <CardTitle>Sistemas Mais Consultados</CardTitle>
                        <CardDescription>Distribuição por sistema</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="system in apiStats.top_systems"
                                :key="system.system"
                                class="flex items-center justify-between"
                            >
                                <div class="font-semibold">{{ system.system }}</div>
                                <div class="flex items-center gap-2">
                                    <div class="text-sm text-muted-foreground">
                                        {{ system.total }} requisições
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="apiStats.top_systems.length === 0"
                                class="py-4 text-center text-sm text-muted-foreground"
                            >
                                Nenhum sistema consultado
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Endpoints 404 -->
            <Card v-if="apiStats.total_404 > 0">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5 text-orange-500" />
                        Endpoints Não Encontrados (404)
                    </CardTitle>
                    <CardDescription>
                        {{ apiStats.total_404 }} requisições retornaram erro 404 nas últimas 24h
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div
                            v-for="endpoint in apiStats.endpoints_404"
                            :key="`${endpoint.http_method}-${endpoint.endpoint}`"
                            class="flex items-center justify-between rounded-md border border-orange-200 bg-orange-50 p-3 dark:border-orange-900 dark:bg-orange-950/20"
                        >
                            <div class="flex items-center gap-2">
                                <Badge variant="outline" class="font-mono text-xs border-orange-300">
                                    {{ endpoint.http_method }}
                                </Badge>
                                <span class="text-sm font-mono">{{ endpoint.endpoint }}</span>
                            </div>
                            <div class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                                {{ endpoint.total }} erros
                            </div>
                        </div>
                        <div
                            v-if="apiStats.endpoints_404.length === 0"
                            class="py-4 text-center text-sm text-muted-foreground"
                        >
                            Nenhum erro 404 registrado
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Gráfico de Requisições por Hora -->
            <Card>
                <CardHeader>
                    <CardTitle>Requisições por Hora</CardTitle>
                    <CardDescription>Distribuição nas últimas 24 horas</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div
                            v-for="hour in apiStats.requests_per_hour"
                            :key="hour.hour"
                            class="flex items-center gap-3"
                        >
                            <div class="w-32 text-sm text-muted-foreground">
                                {{ new Date(hour.hour).toLocaleString('pt-BR', { 
                                    day: '2-digit', 
                                    month: '2-digit', 
                                    hour: '2-digit' 
                                }) }}h
                            </div>
                            <div class="flex-1">
                                <div class="h-6 rounded bg-primary/20" 
                                     :style="{ width: `${(hour.total / Math.max(...apiStats.requests_per_hour.map(h => h.total))) * 100}%` }">
                                </div>
                            </div>
                            <div class="w-16 text-right text-sm font-medium">
                                {{ hour.total }}
                            </div>
                        </div>
                        <div
                            v-if="apiStats.requests_per_hour.length === 0"
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            Nenhuma requisição nas últimas 24 horas
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
