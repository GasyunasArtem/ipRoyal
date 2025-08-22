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
        Schema::table('points_transactions', function (Blueprint $table) {
            // Performance index for date-based queries
            $table->index('created_at');
            
            // Composite index for stats calculations
            $table->index(['created_at', 'is_claimed']);
            
            // Index for user-specific unclaimed transactions
            $table->index(['user_id', 'is_claimed', 'created_at']);
        });

        Schema::table('daily_stats', function (Blueprint $table) {
            // Index for date range queries
            $table->index('date');
            
            // Composite index for period queries
            $table->index(['date', 'created_count']);
        });

        Schema::table('user_profile_answers', function (Blueprint $table) {
            // Index for date-based profile updates check
            $table->index(['user_id', 'updated_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Index for country-based queries
            $table->index('country');
            
            // Index for email lookups (if not already exists)
            if (!Schema::hasIndex('users', ['email'])) {
                $table->index('email');
            }
        });

        Schema::table('wallets', function (Blueprint $table) {
            // Index for user wallet lookups
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points_transactions', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['created_at', 'is_claimed']);
            $table->dropIndex(['user_id', 'is_claimed', 'created_at']);
        });

        Schema::table('daily_stats', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['date', 'created_count']);
        });

        Schema::table('user_profile_answers', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'updated_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['country']);
            if (Schema::hasIndex('users', ['email'])) {
                $table->dropIndex(['email']);
            }
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
