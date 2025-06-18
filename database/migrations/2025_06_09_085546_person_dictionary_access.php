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
    Schema::create('person_dictionaries_access', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // user who has access
        $table->foreignId('person_dictionary_id')->constrained('person_dictionaries')->onDelete('cascade'); // assessment/dictionary they can access
        $table->string('access_level')->default('read'); // e.g. read, write, admin
        $table->timestamps();
        
        $table->unique(['user_id', 'person_dictionary_id']); // prevent duplicate access entries
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_dictionary_access');
    }
};
