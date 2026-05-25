<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('big_boys_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('code')->nullable();
            $table->text('defense_text')->nullable(); // текст защиты проекта
            $table->integer('code_score')->default(0); // оценка кода нейросетью
            $table->integer('defense_score')->default(0); // оценка защиты нейросетью
            $table->integer('total_score')->default(0); // общая оценка
            $table->boolean('is_ai_generated')->default(false); // содержит ли код части, сгенерированные ИИ
            $table->integer('ai_generated_percentage')->default(0); // процент кода, сгенерированного ИИ
            $table->integer('complexity_level')->default(1); // уровень сложности проекта (1-10)
            $table->integer('coins_reward')->default(0); // монетки награды
            $table->integer('experience_points')->default(0); // очки опыта
            $table->boolean('is_approved')->default(false); // одобрен ли проект
            $table->boolean('is_blocked')->default(false); // заблокирован ли проект (маленький код)
            $table->timestamp('blocked_until')->nullable(); // блокировка до конца дня
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('big_boys_projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->integer('line_number')->nullable(); // номер строки кода, к которой относится комментарий
            $table->string('code_snippet')->nullable(); // фрагмент кода
            $table->boolean('is_ai_related')->default(false); // относится ли комментарий к ИИ-части
            $table->timestamps();
        });

        Schema::create('user_project_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('blocked_until')->nullable();
            $table->integer('failed_attempts')->default(0);
            $table->timestamps();
        });

        Schema::create('project_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('big_boys_projects')->onDelete('cascade');
            $table->json('code_metrics')->nullable(); // метрики кода (сложность, качество и т.д.)
            $table->json('defense_metrics')->nullable(); // метрики защиты
            $table->json('ai_detection_results')->nullable(); // результаты детекции ИИ
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_evaluations');
        Schema::dropIfExists('user_project_blocks');
        Schema::dropIfExists('project_comments');
        Schema::dropIfExists('big_boys_projects');
    }
};
