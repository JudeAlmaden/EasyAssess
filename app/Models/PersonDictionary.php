<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonDictionary extends Model
{
    use HasFactory;

    protected $table = 'person_dictionaries';

    protected $fillable = [
        'name',
        'description',
        'member_count',
        'meta_columns',
        'persons_data'
    ];

    /**
     * Relationships (examples):
     * If your dictionary has related models like people or access rights,
     * you can define them here.
     */

    // Example: Dictionary has many people (assuming a `persons` table exists)
    public function persons()
    {
        return $this->hasMany(Person::class);
    }

    // Example: Access control
    public function accesses()
    {
        return $this->hasMany(PersonDictionaryAccess::class);
    }
}
