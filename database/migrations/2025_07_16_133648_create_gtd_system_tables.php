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
        // Contexts table
        Schema::create('contexts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->default('#3b82f6');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['user_id', 'name']);
        });

        // Projects table
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'someday', 'completed', 'cancelled'])->default('active');
            $table->date('due_date')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });

        // Items table (main GTD items)
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['inbox', 'next_action', 'waiting_for', 'someday_maybe', 'reference'])->default('inbox');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->datetime('due_date')->nullable();
            $table->datetime('reminder_date')->nullable();
            $table->integer('energy_level')->default(2); // 1=low, 2=medium, 3=high
            $table->integer('time_estimate')->nullable(); // in minutes
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('context_id')->nullable()->constrained()->onDelete('set null');
            $table->string('waiting_for_person')->nullable();
            $table->date('waiting_since')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type', 'status']);
            $table->index(['user_id', 'context_id']);
            $table->index(['user_id', 'project_id']);
            $table->index(['due_date']);
        });

        // Weekly reviews table
        Schema::create('weekly_reviews', function (Blueprint $table) {
            $table->id();
            $table->date('review_date');
            $table->json('review_data'); // Store review questions and answers
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'review_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reviews');
        Schema::dropIfExists('items');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('contexts');
    }
};
