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
        Schema::create('class_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained();
            $table->string('name');
            $table->string('level')->nullable();
            $table->string('section')->nullable();
            $table->text('description')->nullable();
            $table->integer('max_students')->nullable();
            $table->string('academic_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'name', 'academic_year']);
            $table->index(['school_id', 'level']);
            $table->index(['school_id', 'academic_year']);
            $table->index(['school_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};
