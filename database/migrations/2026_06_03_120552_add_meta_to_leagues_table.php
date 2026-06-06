<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->string('country')->nullable()->after('name');
            $table->string('flag')->nullable()->after('country');
            $table->integer('season')->nullable()->after('flag');
            $table->string('type')->nullable()->after('season');
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn(['country', 'flag', 'season', 'type']);
        });
    }
};
