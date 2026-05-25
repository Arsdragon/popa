@extends('layouts.app')

@section('title', 'Отправить проект - Big Boys Projects')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Отправить новый проект</h2>
                    <p class="mb-0">Отправьте свой масштабный проект для оценки нейросетью</p>
                </div>
                
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('projects.store') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="title">Название проекта *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Описание проекта</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Кратко опишите, что делает ваш проект
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="code">Код проекта *</label>
                            <textarea class="form-control @error('code') is-invalid @enderror" 
                                      id="code" name="code" rows="15" required>{{ old('code') }}</textarea>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Внимание!</strong> Минимальный размер кода - 500 символов. 
                                Маленькие проекты блокируют доступ до конца дня.
                                Код, сгенерированный ИИ, получает меньше баллов.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="defense_text">Защита проекта *</label>
                            <textarea class="form-control @error('defense_text') is-invalid @enderror" 
                                      id="defense_text" name="defense_text" rows="8" required>{{ old('defense_text') }}</textarea>
                            @error('defense_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Напишите план/защиту проекта. Объясните, почему ваш код крутой, 
                                что он делает и почему вы как на коне. 
                                Нейросеть оценивает и код, и защиту!
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Правила оценки:</h5>
                            <ul class="mb-0">
                                <li>Проект должен быть масштабным (мин. 500 символов кода)</li>
                                <li>Нейросеть оценивает и код, и защиту проекта</li>
                                <li>За ИИ-код баллы не начисляются</li>
                                <li>Чем комплекснее проект - тем больше награда</li>
                                <li>Маленькие проекты блокируют доступ до конца дня</li>
                                <li>Можно разблокировать доступ за монетки</li>
                                <li>Монетки за создание проекта идут на счет администратора</li>
                            </ul>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Отправить на оценку
                            </button>
                            <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Назад к списку
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Подсчет символов в коде
    const codeTextarea = document.getElementById('code');
    const defenseTextarea = document.getElementById('defense_text');
    
    function updateCharCount(textarea, minChars) {
        const count = textarea.value.length;
        const helpText = textarea.nextElementSibling.nextElementSibling;
        
        if (count < minChars) {
            helpText.innerHTML = `<strong class="text-danger">Слишком мало символов: ${count}/${minChars}. Проект будет заблокирован!</strong>`;
        } else {
            helpText.innerHTML = `Символов: ${count} (минимум ${minChars})`;
        }
    }
    
    codeTextarea.addEventListener('input', function() {
        updateCharCount(this, 500);
    });
    
    defenseTextarea.addEventListener('input', function() {
        updateCharCount(this, 50);
    });
    
    // Инициализация при заг��узке
    updateCharCount(codeTextarea, 500);
    updateCharCount(defenseTextarea, 50);
</script>
@endpush
@endsection
