<?php

namespace App\Models;

use App\Enums\TaskSubstitutionStatus;
use Database\Factories\TaskSubstitutionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSubstitution extends Model
{
    /** @use HasFactory<TaskSubstitutionFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => TaskSubstitutionStatus::Pending->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskSubstitutionStatus::class,
        ];
    }

    public function vacation(): BelongsTo
    {
        return $this->belongsTo(Vacation::class);
    }

    public function weeklyTask(): BelongsTo
    {
        return $this->belongsTo(WeeklyTask::class);
    }

    public function originalMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'original_member_id');
    }

    public function substituteMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'substitute_member_id');
    }
}
