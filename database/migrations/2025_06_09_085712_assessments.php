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
            
            // Foreign keys
            $table->foreignId('person_dictionary_id')->constrained('person_dictionaries')->onDelete('cascade');
            $table->foreignId('omr_sheet_id')->constrained('omr_sheets')->onDelete('cascade');
            
            $table->string('title')->nullable();               
            $table->text('description')->nullable();          
            $table->json('correct_answers')->nullable();
            $table->json('results')->nullable();                

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

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
