<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $table = 'big_boys_projects';
    
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'code',
        'defense_text',
        'code_score',
        'defense_score',
        'total_score',
        'is_ai_generated',
        'ai_generated_percentage',
        'complexity_level',
        'coins_reward',
        'experience_points',
        'is_approved',
        'is_blocked',
        'blocked_until',
        'submitted_at',
        'evaluated_at',
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
        'is_approved' => 'boolean',
        'is_blocked' => 'boolean',
        'submitted_at' => 'datetime',
        'evaluated_at' => 'datetime',
        'blocked_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class, 'project_id');
    }

    public function evaluation()
    {
        return $this->hasOne(ProjectEvaluation::class, 'project_id');
    }

    public function isBlocked(): bool
    {
        return $this->is_blocked && $this->blocked_until && $this->blocked_until->isFuture();
    }

    public function canBeEvaluated(): bool
    {
        return !$this->isBlocked() && $this->code && $this->defense_text;
    }

    public function calculateTotalScore(): int
    {
        $codeWeight = 0.7;
        $defenseWeight = 0.3;
        
        $total = ($this->code_score * $codeWeight) + ($this->defense_score * $defenseWeight);
        
        // Уменьшаем баллы за ИИ-код
        if ($this->is_ai_generated) {
            $aiPenalty = 1 - ($this->ai_generated_percentage / 100);
            $total *= $aiPenalty;
        }
        
        // Увеличиваем за сложность
        $complexityBonus = 1 + ($this->complexity_level * 0.1);
        $total *= $complexityBonus;
        
        return (int) round($total);
    }

    public function calculateRewards(): array
    {
        $baseCoins = 100;
        $baseExp = 50;
        
        $coins = $baseCoins * ($this->total_score / 100) * $this->complexity_level;
        $exp = $baseExp * ($this->total_score / 100) * $this->complexity_level;
        
        // Уменьшаем награду за ИИ-код
        if ($this->is_ai_generated) {
            $aiPenalty = 1 - ($this->ai_generated_percentage / 200);
            $coins *= $aiPenalty;
            $exp *= $aiPenalty;
        }
        
        return [
            'coins' => (int) max(0, round($coins)),
            'experience' => (int) max(0, round($exp)),
        ];
    }
}
