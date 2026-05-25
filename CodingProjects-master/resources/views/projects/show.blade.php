@extends('layouts.app')

@section('title', $project->title . ' - Big Boys Projects')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h2>{{ $project->title }}</h2>
                    <small class="text-muted">
                        Автор: {{ $project->user->name }}
                        | Отправлен: {{ $project->created_at->format('d.m.Y H:i') }}
                        @if($project->evaluated_at)
                            | Оценен: {{ $project->evaluated_at->format('d.m.Y H:i') }}
                        @endif
                    </small>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if($project->description)
                        <div class="mb-4">
                            <h5>Описание</h5>
                            <p>{{ $project->description }}</p>
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <h5>Защита проекта</h5>
                        <div class="bg-light p-3 rounded">
                            {!! nl2br(e($project->defense_text)) !!}
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Код проекта</h5>
                        <pre class="bg-dark text-light p-3 rounded" style="max-height: 500px; overflow-y: auto;"><code>{{ $project->code }}</code></pre>
                    </div>
                    
                    @if($project->evaluation)
                        <div class="mb-4">
                            <h5>Оценка нейросети</h5>
                            <div class="alert alert-info">
                                {!! nl2br(e($project->evaluation->feedback)) !!}
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Оценка кода</h6>
                                            <div class="progress mb-2" style="height: 25px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: {{ $project->code_score }}%"
                                                     aria-valuenow="{{ $project->code_score }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    {{ $project->code_score }}/100
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Оценка защиты</h6>
                                            <div class="progress mb-2" style="height: 25px;">
                                                <div class="progress-bar bg-info" role="progressbar" 
                                                     style="width: {{ $project->defense_score }}%"
                                                     aria-valuenow="{{ $project->defense_score }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    {{ $project->defense_score }}/100
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <h6>Метрики:</h6>
                                <ul>
                                    <li>Сложность проекта: {{ $project->complexity_level }}/10</li>
                                    @if($project->is_ai_generated)
                                        <li class="text-warning">ИИ-код: {{ $project->ai_generated_percentage }}% (баллы уменьшены)</li>
                                    @endif
                                    <li>Общая оценка: {{ $project->total_score }}/100</li>
                                    <li>Награда: {{ $project->coins_reward }} монеток + {{ $project->experience_points }} опыта</li>
                                    <li>Статус: 
                                        @if($project->is_approved)
                                            <span class="badge badge-success">Одобрен</span>
                                        @elseif($project->is_blocked)
                                            <span class="badge badge-danger">Заблокирован (мало баллов)</span>
                                        @else
                                            <span class="badge badge-warning">На проверке</span>
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> Проект находится на оценке нейросетью...
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Комментарии -->
            <div class="card">
                <div class="card-header">
                    <h5>Комментарии к коду</h5>
                    <small class="text-muted">Указывайте на ИИ-части или комментируйте код</small>
                </div>
                
                <div class="card-body">
                    @if($project->comments->count() > 0)
                        @foreach($project->comments as $comment)
                            <div class="media mb-3">
                                <div class="media-body">
                                    <h6 class="mt-0">
                                        {{ $comment->user->name }}
                                        <small class="text-muted">{{ $comment->created_at->format('d.m.Y H:i') }}</small>
                                        @if($comment->is_ai_related)
                                            <span class="badge badge-warning ml-2">ИИ-часть</span>
                                        @endif
                                    </h6>
                                    
                                    @if($comment->line_number)
                                        <div class="alert alert-light border">
                                            <small class="text-muted">Строка {{ $comment->line_number }}:</small>
                                            @if($comment->code_snippet)
                                                <pre class="mb-1"><code>{{ $comment->code_snippet }}</code></pre>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <p>{{ $comment->comment }}</p>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr>
                            @endif
                        @endforeach
                    @else
                        <p class="text-muted">Пока нет комментариев. Будьте первым!</p>
                    @endif
                    
                    @auth
                        <hr>
                        <h6>Добавить комментарий</h6>
                        <form method="POST" action="{{ route('projects.comment', $project->id) }}">
                            @csrf
                            
                            <div class="form-group">
                                <textarea class="form-control" name="comment" rows="3" 
                                          placeholder="Ваш комментарий..." required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="line_number">Номер строки (если относится к коду)</label>
                                        <input type="number" class="form-control" id="line_number" 
                                               name="line_number" min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="code_snippet">Фрагмент кода</label>
                                        <input type="text" class="form-control" id="code_snippet" 
                                               name="code_snippet" placeholder="if (x > 5) { ... }">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="is_ai_related" 
                                       name="is_ai_related" value="1">
                                <label class="form-check-label" for="is_ai_related">
                                    Комментарий относится к ИИ-части кода
                                </label>
                                <small class="form-text text-muted">
                                    Укажите, если комментируете часть кода, сгенерированную ИИ
                                </small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-comment"></i> Добавить комментарий
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <a href="{{ route('login') }}">Войдите</a>, чтобы оставлять комментарии
                        </div>
                    @endauth
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Статистика проекта -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Статистика проекта</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Сложность
                            <span class="badge badge-primary badge-pill">{{ $project->complexity_level }}/10</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Общая оценка
                            <span class="badge badge-success badge-pill">{{ $project->total_score }}/100</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Награда (монетки)
                            <span class="badge badge-warning badge-pill">
                                <i class="fas fa-coins"></i> {{ $project->coins_reward }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Награда (опыт)
                            <span class="badge badge-info badge-pill">
                                <i class="fas fa-star"></i> {{ $project->experience_points }}
                            </span>
                        </li>
                        @if($project->is_ai_generated)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                ИИ-код
                                <span class="badge badge-warning badge-pill">{{ $project->ai_generated_percentage }}%</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Комментарии
                            <span class="badge badge-secondary badge-pill">{{ $project->comments->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Правила -->
            <div class="card">
                <div class="card-header">
                    <h5>Правила оценки</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            <strong>Масштабный код:</strong> мин. 500 символов
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            <strong>Оценка кода и защиты:</strong> 70%/30%
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times text-danger"></i> 
                            <strong>ИИ-код:</strong> баллы уменьшаются
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-arrow-up text-info"></i> 
                            <strong>Сложность:</strong> увеличивает награду
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-lock text-warning"></i> 
                            <strong>Блокировка:</strong> за маленькие проекты
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-coins text-warning"></i> 
                            <strong>Разблокировка:</strong> 50 монеток
                        </li>
                    </ul>
                    
                    @if($project->is_blocked)
                        <div class="alert alert-danger mt-3">
                            <strong>Проект заблокирован!</strong> Слишком мало баллов.
                        </div>
                    @endif
                    
                    @if($project->is_approved)
                        <div class="alert alert-success mt-3">
                            <strong>Проект одобрен!</strong> Награды начислены.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
