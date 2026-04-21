<?php

namespace App\Models;

use Database\Factories\WeeklyTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyTask extends Model
{
    /** @use HasFactory<WeeklyTaskFactory> */
    use HasFactory;

    public function taskSkills(): HasMany
    {
        return $this->hasMany(MemberTaskSkill::class);
    }

    public function owners(): HasMany
    {
        return $this->hasMany(WeeklyTaskOwner::class);
    }

    public function substitutions(): HasMany
    {
        return $this->hasMany(TaskSubstitution::class);
    }
}
