<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonDictionaryAccess extends Model
{
    use HasFactory;

    protected $table = 'person_dictionaries_access';

    protected $fillable = [
        'user_id',
        'person_dictionary_id',
        'access_level',
    ];

    /**
     * Relationships
     */

    // The user who has access
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // The person dictionary being accessed
    public function personDictionary()
    {
        return $this->belongsTo(PersonDictionary::class);
    }
}
