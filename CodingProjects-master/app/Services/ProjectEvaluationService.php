<?php

namespace App\Services;

use App\Project;
use Illuminate\Support\Facades\Http;

class ProjectEvaluationService
{
    public function evaluate(Project $project)
    {
        $codeScore = $this->evaluateCodeQuality($project->code);
        $defenseScore = $this->evaluateDefenseText($project->defense_text);
        $aiDetection = $this->detectAIContent($project->code);
        
        return [
            'code_score' => $codeScore,
            'defense_score' => $defenseScore,
            'ai_detection' => $aiDetection,
            'complexity' => $this->calculateComplexity($project->code),
        ];
    }
    
    private function evaluateCodeQuality(string $code): int
    {
        $score = 50;
        $lines = count(explode("\n", $code));
        $score += min(30, $lines / 10);
        if (strpos($code, 'function ') !== false) $score += 10;
        if (strpos($code, 'class ') !== false) $score += 15;
        if (strpos($code, 'if ') !== false) $score += 5;
        if (strpos($code, 'for ') !== false) $score += 5;
        return min(100, $score);
    }
    
    private function evaluateDefenseText(string $text): int
    {
        $score = 50;
        $length = strlen($text);
        $score += min(30, $length / 20);
        $keywords = ['алгоритм', 'структура', 'оптимизация', 'решение'];
        foreach ($keywords as $keyword) {
            if (stripos($text, $keyword) !== false) $score += 5;
        }
        return min(100, $score);
    }
    
    private function detectAIContent(string $code): array
    {
        $patterns = ['Certainly', 'As an AI', 'I cannot', 'based on the'];
        $isAi = false;
        $percentage = 0;
        foreach ($patterns as $pattern) {
            if (stripos($code, $pattern) !== false) {
                $isAi = true;
                $percentage += 20;
            }
        }
        if (preg_match_all('/\/\/.*(AI|assistant).*/i', $code, $matches)) {
            $isAi = true;
            $percentage += count($matches[0]) * 10;
        }
        return [
            'is_ai' => $isAi,
            'percentage' => min(100, $percentage),
            'confidence' => $percentage > 0 ? 0.8 : 0.2,
        ];
    }
    
    private function calculateComplexity(string $code): int
    {
        $lines = count(explode("\n", $code));
        $complexity = 1;
        if ($lines > 1000) $complexity = 10;
        elseif ($lines > 500) $complexity = 8;
        elseif ($lines > 200) $complexity = 6;
        elseif ($lines > 100) $complexity = 4;
        elseif ($lines > 50) $complexity = 2;
        $complexity += min(5, substr_count($code, 'function ') + substr_count($code, 'class '));
        return min(10, $complexity);
    }
}
