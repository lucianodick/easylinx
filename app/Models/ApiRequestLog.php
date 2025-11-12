<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    protected $fillable = [
        'ip_address',
        'endpoint',
        'http_method',
        'status_code',
        'request_params',
        'system',
        'cnpj',
        'machine_name',
        'cache_hit',
        'response_time_ms',
        'libraries_count',
        'user_agent',
    ];

    protected $casts = [
        'cache_hit' => 'boolean',
        'response_time_ms' => 'integer',
        'libraries_count' => 'integer',
        'status_code' => 'integer',
        'request_params' => 'array',
    ];
}
