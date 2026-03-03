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
        Schema::create('recipe_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->unsignedTinyInteger('stars');
            $table->text('review')->nullable();
            $table->boolean('turned_out_well')->nullable();
            $table->text('changes')->nullable();
            $table->boolean('would_cook_again')->nullable();
            $table->timestamps();

            $table->unique(['recipe_id', 'user_id']);
            $table->unique(['recipe_id', 'ip_hash']);
            $table->index(['recipe_id', 'stars']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ratings');
    }
};
