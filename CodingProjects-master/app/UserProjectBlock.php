<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProjectBlock extends Model
{
    protected $table = 'user_project_blocks';
    
    protected $fillable = [
        'user_id',
        'blocked_until',
        'failed_attempts',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_until && $this->blocked_until->isFuture();
    }

    public function incrementFailedAttempts(): void
    {
        $this->failed_attempts++;
        
        if ($this->failed_attempts >= 3) {
            $this->blocked_until = now()->endOfDay();
        }
        
        $this->save();
    }

    public function resetBlock(): void
    {
        $this->blocked_until = null;
        $this->failed_attempts = 0;
        $this->save();
    }
}
