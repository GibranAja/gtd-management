<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'due_date',
        'reminder_date',
        'energy_level',
        'time_estimate',
        'notes',
        'user_id',
        'project_id',
        'context_id',
        'waiting_for_person',
        'waiting_since',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_date' => 'datetime',
        'waiting_since' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    // Scopes for different GTD views
    public function scopeInbox(Builder $query): Builder
    {
        return $query->where('type', 'inbox')->where('status', 'active');
    }

    public function scopeNextActions(Builder $query): Builder
    {
        return $query->where('type', 'next_action')->where('status', 'active');
    }

    public function scopeWaitingFor(Builder $query): Builder
    {
        return $query->where('type', 'waiting_for')->where('status', 'active');
    }

    public function scopeSomedayMaybe(Builder $query): Builder
    {
        return $query->where('type', 'someday_maybe')->where('status', 'active');
    }

    public function scopeReference(Builder $query): Builder
    {
        return $query->where('type', 'reference');
    }

    public function scopeByContext(Builder $query, $contextId): Builder
    {
        return $query->where('context_id', $contextId);
    }

    public function scopeByEnergyLevel(Builder $query, $level): Builder
    {
        return $query->where('energy_level', $level);
    }

    public function scopeByTimeEstimate(Builder $query, $maxMinutes): Builder
    {
        return $query->where('time_estimate', '<=', $maxMinutes);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', 'active');
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_date', today())
            ->where('status', 'active');
    }

    public function scopeDueThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'active');
    }
}
