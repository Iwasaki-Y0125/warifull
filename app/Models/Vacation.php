<?php

namespace App\Models;

use Database\Factories\VacationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacation extends Model
{
    /** @use HasFactory<VacationFactory> */
    use HasFactory;

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function substitutions(): HasMany
    {
        return $this->hasMany(TaskSubstitution::class);
    }
}
