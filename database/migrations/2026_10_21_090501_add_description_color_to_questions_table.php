<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('questions', 'description_color')) {
            return;
        }

        Schema::table('questions', function (Blueprint $table) {
            // Цвет текста описания: null — обычный серый, иначе название из
            // набора, который понимает публичная форма (red, blue).
            $table->string('description_color', 16)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('questions', 'description_color')) {
            return;
        }

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('description_color');
        });
    }
};
