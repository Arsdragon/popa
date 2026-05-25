<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectEvaluation extends Model
{
    protected $table = 'project_evaluations';
    
    protected $fillable = [
        'project_id',
        'code_metrics',
        'defense_metrics',
        'ai_detection_results',
        'feedback',
    ];

    protected $casts = [
        'code_metrics' => 'array',
        'defense_metrics' => 'array',
        'ai_detection_results' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
