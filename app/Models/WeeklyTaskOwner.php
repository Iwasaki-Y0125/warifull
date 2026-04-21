<?php

namespace App\Models;

use App\Enums\WeeklyTaskOwnerRole;
use Database\Factories\WeeklyTaskOwnerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyTaskOwner extends Model
{
    /** @use HasFactory<WeeklyTaskOwnerFactory> */
    use HasFactory;

    protected $attributes = [
        'role' => WeeklyTaskOwnerRole::Main->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => WeeklyTaskOwnerRole::class,
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function weeklyTask(): BelongsTo
    {
        return $this->belongsTo(WeeklyTask::class);
    }
}
