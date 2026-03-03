<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recipe_comments', function (Blueprint $table): void {
            // Drop the existing FK constraint first
            $table->dropForeign(['user_id']);

            // Make user_id nullable (allow guest comments)
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Re-add FK with nullOnDelete behavior
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Add guest_name column for non-registered commenters
            $table->string('guest_name', 100)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('recipe_comments', function (Blueprint $table): void {
            $table->dropColumn('guest_name');

            // Restore non-nullable user_id
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
