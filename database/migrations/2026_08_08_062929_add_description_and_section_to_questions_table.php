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
        if (! Schema::hasColumn('questions', 'description')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->text('description')->nullable()->after('title');
            });
        }

        Schema::table('questions', function (Blueprint $table) {
            $table->enum('type', ['text', 'textarea', 'radio', 'checkbox', 'select', 'scale', 'date', 'section'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('type', ['text', 'textarea', 'radio', 'checkbox', 'select', 'scale', 'date'])->change();
        });

        if (Schema::hasColumn('questions', 'description')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
