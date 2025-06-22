<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'answer_key',
        'answers',
        'person_dictionary_snapshot',
        'omr_sheet_snapshot',
    ];

    // Relationships
    public function personDictionary()
    {
        return $this->belongsTo(PersonDictionary::class);
    }

    public function omrSheet()
    {
        return $this->belongsTo(OmrSheet::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
