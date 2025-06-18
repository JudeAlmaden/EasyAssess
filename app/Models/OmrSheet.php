<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmrSheet extends Model
{
    use HasFactory;

    protected $table = 'omr_sheets';
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'json_data',
    ];

    // {
    //   "OMRSheet": {
    //     "HTML": "<div class='...'>...</div>",
    //     "Format": "Letter",
    //     "MCQ": [...],
    //     "Freeform": [],
    //     "Blanks": []
    //   }
    // }

    /**
     * Relationships
     */

    // The user who owns the OMR sheet
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
