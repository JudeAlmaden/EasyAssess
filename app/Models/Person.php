<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'persons';

    // Allow mass assignment for these fields
    protected $fillable = [
        'person_dictionary_id',
        'data',
    ];


    // Relationship to PersonDictionary
    public function personDictionary()
    {
        return $this->belongsTo(PersonDictionary::class);
    }
}
