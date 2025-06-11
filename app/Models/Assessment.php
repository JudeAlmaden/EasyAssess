<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'person_dictionary_id',
        'omr_sheet_id',
        'title',
        'description',
        'correct_answers',
        'results',
        'created_by',
    ];

    protected $casts = [
        'results' => 'array',
        'correct_answers'=>'array'
    ];

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
