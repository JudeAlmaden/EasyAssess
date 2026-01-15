<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentAccess extends Model
{
    protected $table = 'assessment_access';

    protected $fillable = [
        'assessment_id',
        'user_id',
        'access_level',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
