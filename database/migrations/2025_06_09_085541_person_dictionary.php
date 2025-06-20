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
            $table->string('name'); 
            $table->text('description')->nullable();  
            $table->json('persons_data')->nullable();
            $table->unsignedInteger('member_count')->default(0);
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_dictionaries');
    }
};
