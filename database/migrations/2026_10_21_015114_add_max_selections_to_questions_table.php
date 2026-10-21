<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('questions', 'max_selections')) {
            return;
        }

        Schema::table('questions', function (Blueprint $table) {
            // Предел числа отметок у вопроса с множественным выбором.
            // null — без ограничения; для прочих типов всегда null.
            $table->unsignedSmallInteger('max_selections')->nullable()->after('options');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('questions', 'max_selections')) {
            return;
        }

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('max_selections');
        });
    }
};
