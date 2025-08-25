<?php

declare(strict_types=1);

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
        Schema::create('grades', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('subject_id')->constrained();
            $table->foreignUuid('student_id')->constrained('users');
            $table->foreignUuid('class_group_id')->constrained();
            $table->foreignUuid('teacher_id')->constrained('users');
            $table->decimal('value', 4, 2);
            $table->decimal('max_value', 4, 2);
            $table->decimal('coefficient', 3, 2);
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->date('given_at');
            $table->string('academic_year');
            $table->boolean('is_active');
            $table->timestamps();

            $table->index(['user_id', 'subject_id', 'academic_year']);
            $table->index(['class_group_id', 'subject_id']);
            $table->index(['is_active', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
