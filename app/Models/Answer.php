<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $table = 'answers';

    protected $fillable = [
        'assessment_id',
        'person_id',
        'user_id',
        'answer_json',
        'score',
    ];

    protected $casts = [
        'answer_json' => 'array',
        'score' => 'float',
    ];

    // Relationships

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
