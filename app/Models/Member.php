<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    public function taskSkills(): HasMany
    {
        return $this->hasMany(MemberTaskSkill::class);
    }

    public function weeklyTaskOwners(): HasMany
    {
        return $this->hasMany(WeeklyTaskOwner::class);
    }

    public function originalTaskSubstitutions(): HasMany
    {
        return $this->hasMany(TaskSubstitution::class, 'original_member_id');
    }

    public function substituteTaskSubstitutions(): HasMany
    {
        return $this->hasMany(TaskSubstitution::class, 'substitute_member_id');
    }
}
