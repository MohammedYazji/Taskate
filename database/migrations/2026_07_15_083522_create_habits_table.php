<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon', 10)->default('🎯');
            $table->string('frequency_type')->default('daily');
            $table->json('frequency_config')->nullable();
            $table->string('goal_type')->default('boolean');
            $table->json('goal_config')->nullable();
            $table->date('start_date')->nullable();
            $table->integer('goal_days')->nullable();
            $table->string('section')->default('Others');
            $table->json('reminders')->nullable();
            $table->boolean('auto_popup')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habits');
    }
};
