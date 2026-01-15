<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentShareCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'user_id',
        'code',
        'enabled',
        'expires_at',
        
    ];

    // Relationships

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
