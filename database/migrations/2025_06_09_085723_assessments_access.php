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
        Schema::create('assessment_access', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->enum('access_level', ['owner', 'viewer', 'admin'])->default('viewer');

            // New column to indicate if the access is still active/open
            $table->boolean('is_open')->default(true);
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
            $table->unique(['assessment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_access');
    }
};
