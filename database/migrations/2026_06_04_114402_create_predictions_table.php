<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixture_id')->unique();
            $table->string('tip');                       // ex: "Home Win", "Over 2.5 Goals", "BTTS"
            $table->string('predicted_score')->nullable(); // ex: "2-1"
            $table->unsignedTinyInteger('confidence')->default(3); // 1-5
            $table->text('body')->nullable();            // analisa (markdown)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
