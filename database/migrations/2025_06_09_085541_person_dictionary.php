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
        Schema::create('person_dictionaries', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Group name (e.g. "Grade 10 - Section A")
            $table->text('description')->nullable(); // Optional description
            $table->unsignedInteger('member_count')->default(0); // Number of people in the group
            $table->timestamps(); // created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_dictionaries');
    }
};
