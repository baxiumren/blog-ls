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
        Schema::create('match_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixture_id')->index();
            $table->string('choice', 8); // home | draw | away
            $table->string('visitor', 40);
            $table->timestamps();
            $table->unique(['fixture_id', 'visitor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_votes');
    }
};
