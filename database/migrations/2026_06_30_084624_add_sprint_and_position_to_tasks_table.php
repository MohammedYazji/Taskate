<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'sprint_id')) {
                $table->foreignId('sprint_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('tasks', 'position')) {
                $table->integer('position')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['sprint_id']);
            $table->dropColumn(['sprint_id', 'position']);
        });
    }
};
