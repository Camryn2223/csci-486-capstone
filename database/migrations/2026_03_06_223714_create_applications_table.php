<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_position_id')->constrained('job_positions')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('application_templates')->cascadeOnDelete();
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone')->nullable();
            $table->enum('status', ['submitted', 'under_review', 'needs_chairman_review', 'no_longer_under_consideration', 'withdrawn'])->default('submitted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};