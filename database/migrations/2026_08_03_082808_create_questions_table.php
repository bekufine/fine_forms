<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['text', 'textarea', 'radio', 'checkbox', 'select', 'scale', 'date']);
            $table->string('title');
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->jsonb('options')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index(['form_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
