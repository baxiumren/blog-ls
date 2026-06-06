<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->unsignedBigInteger('api_id')->nullable()->unique()->after('id');
            $table->string('logo_url')->nullable()->after('color');
        });
    
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('api_id')->nullable()->unique()->after('id');
            $table->string('logo_url')->nullable()->after('short_name');
        });
    }
    
    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn(['api_id', 'logo_url']);
        });
    
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['api_id', 'logo_url']);
        });
    }
};
