<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectComment extends Model
{
    protected $table = 'project_comments';
    
    protected $fillable = [
        'project_id',
        'user_id',
        'comment',
        'line_number',
        'code_snippet',
        'is_ai_related',
    ];

    protected $casts = [
        'is_ai_related' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
