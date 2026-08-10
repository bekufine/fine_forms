<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
        });

        DB::statement("ALTER TABLE questions MODIFY type ENUM('text', 'textarea', 'radio', 'checkbox', 'select', 'scale', 'date', 'section') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE questions MODIFY type ENUM('text', 'textarea', 'radio', 'checkbox', 'select', 'scale', 'date') NOT NULL");

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
