<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_date',
        'review_data',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'review_date' => 'date',
        'review_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
