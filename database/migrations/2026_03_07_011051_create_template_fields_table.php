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
        Schema::create('template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('application_templates')->onDelete('cascade');
            $table->string('label');
            $table->enum('type', ['text', 'textarea', 'select', 'checkbox', 'radio', 'file', 'date']);
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('file_multiple')->default(false);
            $table->integer('file_max')->nullable();
            $table->integer('char_max')->nullable();
            $table->integer('file_size_max')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_fields');
    }
};
