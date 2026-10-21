<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['type', 'title', 'description', 'is_required', 'order', 'options', 'max_selections', 'is_archived'])]
class Question extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_archived' => 'boolean',
            'order' => 'integer',
            'options' => 'array',
            'max_selections' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }
}
