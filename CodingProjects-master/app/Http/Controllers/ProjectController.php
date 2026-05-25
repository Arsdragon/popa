<?php

namespace App\Http\Controllers;

use App\Project;
use App\ProjectComment;
use App\UserProjectBlock;
use App\CoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('user')
            ->where('is_approved', true)
            ->orderBy('total_score', 'desc')
            ->paginate(20);
            
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $user = Auth::user();
        $block = UserProjectBlock::where('user_id', $user->id)->first();
        
        if ($block && $block->isBlocked()) {
            return redirect()->route('projects.index')
                ->with('error', 'Вы заблокированы до конца дня за отправку маленьких проектов. Можете разблокироваться за монетки.');
        }
        
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $block = UserProjectBlock::where('user_id', $user->id)->first();
        
        if ($block && $block->isBlocked()) {
            return redirect()->route('projects.index')
                ->with('error', 'Вы заблокированы до конца дня.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'required|string|min:100', // минимальный размер кода
            'defense_text' => 'required|string|min:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Проверяем размер кода
        $codeSize = strlen($request->code);
        $minCodeSize = 500; // минимальный размер для "масштабного" проекта
        
        if ($codeSize < $minCodeSize) {
            if (!$block) {
                $block = UserProjectBlock::create([
                    'user_id' => $user->id,
                    'failed_attempts' => 1,
                ]);
            } else {
                $block->incrementFailedAttempts();
            }
            
            return redirect()->route('projects.index')
                ->with('error', 'Код слишком маленький. Проект не считается масштабным. ' . 
                       ($block->failed_attempts >= 3 ? 'Вы заблокированы до конца дня.' : ''));
        }

        $project = Project::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'code' => $request->code,
            'defense_text' => $request->defense_text,
            'submitted_at' => now(),
            'complexity_level' => $this->estimateComplexity($request->code),
        ]);

        // Сбрасываем блокировку при успешной отправке
        if ($block) {
            $block->resetBlock();
        }

        // Запускаем оценку проекта
        $this->evaluateProject($project);

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Проект отправлен на оценку!');
    }

    public function show($id)
    {
        $project = Project::with(['user', 'comments.user', 'evaluation'])
            ->findOrFail($id);
            
        return view('projects.show', compact('project'));
    }

    public function addComment(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|min:5',
            'line_number' => 'nullable|integer',
            'code_snippet' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'line_number' => $request->line_number,
            'code_snippet' => $request->code_snippet,
            'is_ai_related' => $request->has('is_ai_related'),
        ]);

        return redirect()->back()->with('success', 'Комментарий добавлен');
    }

    public function unlockAccess(Request $request)
    {
        $user = Auth::user();
        $block = UserProjectBlock::where('user_id', $user->id)->first();
        
        if (!$block || !$block->isBlocked()) {
            return redirect()->route('projects.index')
                ->with('error', 'У вас нет активной блокировки');
        }

        $unlockCost = 50; // стоимость разблокировки
        
        if ($user->coins < $unlockCost) {
            return redirect()->route('projects.index')
                ->with('error', 'Недостаточно монеток для разблокировки');
        }

        // Списываем монетки
        $user->coins -= $unlockCost;
        $user->save();

        // Создаем транзакцию
        CoinTransaction::create([
            'user_id' => $user->id,
            'amount' => -$unlockCost,
            'type' => 'project_unlock',
            'description' => 'Разблокировка доступа к отправке проектов',
        ]);

        // Разблокируем пользователя
        $block->resetBlock();

        return redirect()->route('projects.create')
            ->with('success', 'Доступ разблокирован! Можете отправить новый проект.');
    }

    private function estimateComplexity(string $code): int
    {
        // Простая оценка сложности по количеству строк и структуре
        $lines = count(explode("\n", $code));
        $functions = substr_count($code, 'function ');
        $classes = substr_count($code, 'class ');
        
        $complexity = 1;
        
        if ($lines > 1000) $complexity = 10;
        elseif ($lines > 500) $complexity = 8;
        elseif ($lines > 200) $complexity = 6;
        elseif ($lines > 100) $complexity = 4;
        elseif ($lines > 50) $complexity = 2;
        
        // Увеличиваем сложность за функции и классы
        $complexity += min(5, $functions + $classes);
        
        return min(10, $complexity);
    }

    private function evaluateProject(Project $project)
    {
        $service = new \App\Services\ProjectEvaluationService();
        $evaluation = $service->evaluate($project);
        
        $project->code_score = $evaluation['code_score'];
        $project->defense_score = $evaluation['defense_score'];
        $project->is_ai_generated = $evaluation['ai_detection']['is_ai'];
        $project->ai_generated_percentage = $evaluation['ai_detection']['percentage'];
        $project->complexity_level = $evaluation['complexity'];
        $project->total_score = $project->calculateTotalScore();
        
        $rewards = $project->calculateRewards();
        $project->coins_reward = $rewards['coins'];
        $project->experience_points = $rewards['experience'];
        
        $project->is_approved = $project->total_score >= 30;
        $project->is_blocked = $project->total_score < 10;
        
        if ($project->is_blocked) {
            $project->blocked_until = now()->endOfDay();
        }
        
        $project->evaluated_at = now();
        $project->save();
        
        $project->evaluation()->create([
            'code_metrics' => [
                'lines_of_code' => count(explode("\n", $project->code)),
                'functions_count' => substr_count($project->code, 'function ') + substr_count($project->code, 'def '),
                'complexity_score' => $evaluation['code_score'],
            ],
            'defense_metrics' => [
                'text_length' => strlen($project->defense_text),
                'quality_score' => $evaluation['defense_score'],
            ],
            'ai_detection_results' => $evaluation['ai_detection'],
            'feedback' => $this->generateFeedback($evaluation['code_score'], $evaluation['defense_score'], $evaluation['ai_detection']),
        ]);
        
        if ($project->is_approved) {
            $this->awardUser($project);
        }
    }

    private function estimateComplexity(string $code): int
    {
        return (new \App\Services\ProjectEvaluationService())->calculateComplexity($code);
    }
            $percentage += count($matches[0]) * 10;
        }
        
        return [
            'is_ai' => $isAi,
            'percentage' => min(100, $percentage),
            'confidence' => $percentage > 0 ? 0.8 : 0.2,
        ];
    }

    private function generateFeedback(int $codeScore, int $defenseScore, array $aiDetection): string
    {
        $feedback = [];
        
        if ($codeScore >= 70) {
            $feedback[] = "Отличный код! Хорошая структура и логика.";
        } elseif ($codeScore >= 40) {
            $feedback[] = "Неплохой код, но есть куда расти.";
        } else {
            $feedback[] = "Код требует доработки. Уделите внимание качеству.";
        }
        
        if ($defenseScore >= 70) {
            $feedback[] = "Отличная защита проекта! Четко изложены идеи.";
        } elseif ($defenseScore >= 40) {
            $feedback[] = "Защита неплохая, но можно более подробно.";
        } else {
            $feedback[] = "Защита требует улучшения. Опишите проект детальнее.";
        }
        
        if ($aiDetection['is_ai']) {
            $feedback[] = "Обнаружены признаки ИИ-кода. Баллы за эти части не начислены.";
        }
        
        return implode(' ', $feedback);
    }

    private function awardUser(Project $project)
    {
        $user = $project->user;
        
        // Начисляем монетки
        $user->coins += $project->coins_reward;
        
        // Начисляем опыт (если есть такое поле в модели User)
        if (property_exists($user, 'experience')) {
            $user->experience += $project->experience_points;
        }
        
        $user->save();
        
        // Создаем транзакцию для монеток
        CoinTransaction::create([
            'user_id' => $user->id,
            'amount' => $project->coins_reward,
            'type' => 'project_reward',
            'description' => "Награда за проект: {$project->title}",
        ]);
        
        // Монетки уходят на ваш счет (администратору)
        // Здесь нужно указать ID администратора
        $adminId = 1; // ID администратора
        
        CoinTransaction::create([
            'user_id' => $adminId,
            'amount' => $project->coins_reward,
            'type' => 'project_creation_fee',
            'description' => "Комиссия за создание проекта: {$project->title}",
        ]);
    }
}
