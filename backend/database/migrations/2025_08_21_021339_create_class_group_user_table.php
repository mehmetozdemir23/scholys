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
        Schema::create('class_group_user', function (Blueprint $table): void {
            $table->foreignUuid('class_group_id')->constrained();
            $table->foreignUuid('user_id')->constrained();
            $table->date('assigned_at');
            $table->timestamps();

            $table->primary(['class_group_id', 'user_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_group_user');
    }
};
