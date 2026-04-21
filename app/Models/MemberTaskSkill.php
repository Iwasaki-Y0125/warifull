<?php

namespace App\Models;

use Database\Factories\MemberTaskSkillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberTaskSkill extends Model
{
    /** @use HasFactory<MemberTaskSkillFactory> */
    use HasFactory;

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function weeklyTask(): BelongsTo
    {
        return $this->belongsTo(WeeklyTask::class);
    }
}
