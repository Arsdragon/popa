@extends('layouts.app')

@section('title', 'Big Boys Projects - Магазин проектов')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Big Boys Projects</h2>
                    <p class="mb-0">Магазин масштабных проектов. Отправляйте свой код и защиту, получайте оценку нейросети и награды!</p>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <a href="{{ route('projects.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Отправить новый проект
                        </a>
                        
                        @php
                            $block = auth()->user()->projectBlocks()->first();
                        @endphp
                        
                        @if($block && $block->isBlocked())
                            <div class="alert alert-warning mt-3">
                                <strong>Внимание!</strong> Вы заблокированы до конца дня за отправку маленьких проектов.
                                <a href="{{ route('projects.unlock') }}" class="btn btn-sm btn-outline-warning ml-2">
                                    Разблокировать за 50 монеток
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    <div class="row">
                        @foreach($projects as $project)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <a href="{{ route('projects.show', $project->id) }}">
                                                {{ $project->title }}
                                            </a>
                                        </h5>
                                        <small class="text-muted">
                                            Автор: {{ $project->user->name }}
                                            | Опубликован: {{ $project->created_at->format('d.m.Y') }}
                                        </small>
                                    </div>
                                    
                                    <div class="card-body">
                                        @if($project->description)
                                            <p class="card-text">{{ Str::limit($project->description, 150) }}</p>
                                        @endif
                                        
                                        <div class="mb-2">
                                            <span class="badge badge-info">Сложность: {{ $project->complexity_level }}/10</span>
                                            @if($project->is_ai_generated)
                                                <span class="badge badge-warning">ИИ-код: {{ $project->ai_generated_percentage }}%</span>
                                            @endif
                                        </div>
                                        
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $project->total_score }}%"
                                                 aria-valuenow="{{ $project->total_score }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                Оценка: {{ $project->total_score }}/100
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted">
                                                    Код: {{ $project->code_score }}/100
                                                </small>
                                            </div>
                                            <div>
                                                <small class="text-muted">
                                                    Защита: {{ $project->defense_score }}/100
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-coins"></i> {{ $project->coins_reward }} монеток
                                                </span>
                                                <span class="badge badge-secondary ml-2">
                                                    <i class="fas fa-star"></i> {{ $project->experience_points }} опыта
                                                </span>
                                            </div>
                                            <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary">
                                                Подробнее
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $projects->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
