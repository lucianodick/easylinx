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
        Schema::table('libraries', function (Blueprint $table) {
            $table->string('system', 100)->default('')->after('id');
            $table->dropUnique(['name']);
            $table->unique(['system', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropUnique(['system', 'name']);
            $table->unique(['name']);
            $table->dropColumn('system');
        });
    }
};
