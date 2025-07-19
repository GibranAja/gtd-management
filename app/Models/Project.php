<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(Item::class)->where('status', 'active');
    }

    public function nextActions(): HasMany
    {
        return $this->hasMany(Item::class)
            ->where('type', 'next_action')
            ->where('status', 'active');
    }

    public function getProgressPercentageAttribute(): int
    {
        $total = $this->items()->count();
        if ($total === 0) return 0;
        
        $completed = $this->items()->where('status', 'completed')->count();
        return round(($completed / $total) * 100);
    }
}
