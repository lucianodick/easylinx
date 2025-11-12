<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->string('endpoint', 255)->after('ip_address')->default('/api/library-versions');
            $table->string('http_method', 10)->after('endpoint')->default('GET');
            $table->integer('status_code')->after('response_time_ms')->default(200);
            $table->text('request_params')->nullable()->after('status_code'); // JSON dos parâmetros
            
            // Índice para consultas por endpoint
            $table->index('endpoint');
            $table->index('status_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->dropIndex(['endpoint']);
            $table->dropIndex(['status_code']);
            $table->dropColumn(['endpoint', 'http_method', 'status_code', 'request_params']);
        });
    }
};
