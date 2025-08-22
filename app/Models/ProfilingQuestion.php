<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilingQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'text',
        'type',
        'options',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserProfileAnswer::class, 'question_id');
    }
}
