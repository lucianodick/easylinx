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
        Schema::table('library_versions', function (Blueprint $table) {
            $table->string('download_url_primary', 500)->nullable()->after('version');
            $table->string('download_url_secondary', 500)->nullable()->after('download_url_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_versions', function (Blueprint $table) {
            $table->dropColumn(['download_url_primary', 'download_url_secondary']);
        });
    }
};
