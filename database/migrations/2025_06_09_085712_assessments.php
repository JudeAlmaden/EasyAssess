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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();

            // Make foreign keys nullable
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->json('answer_key')->nullable();
            $table->json('answers')->nullable();
            $table->json('person_dictionary_snapshot')->nullable();
            $table->json('omr_sheet_snapshot')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
