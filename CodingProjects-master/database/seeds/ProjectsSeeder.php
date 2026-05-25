<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;
use App\Project;
use App\User;

class ProjectsSeeder extends Seeder
{
    public function run()
    {
        $users = User::take(5)->get();
        if ($users->isEmpty()) return;
        
        $projects = [
            [
                'title' => 'Умный калькулятор на Python',
                'description' => 'Калькулятор с поддержкой сложных математических операций',
                'code' => 'import tkinter as tk
import math

class SmartCalculator:
    def __init__(self):
        self.root = tk.Tk()
        self.root.title("Умный калькулятор")
        self.display = tk.Entry(self.root, width=30, font=("Arial", 16))
        self.display.grid(row=0, column=0, columnspan=4, padx=10, pady=10)
        
        buttons = ["7","8","9","/","4","5","6","*","1","2","3","-","0",".","=","+","C","√","x²","sin"]
        
        row = 1
        col = 0
        for button in buttons:
            cmd = lambda x=button: self.button_click(x)
            tk.Button(self.root, text=button, width=5, height=2, font=("Arial", 12), command=cmd).grid(row=row, column=col, padx=5, pady=5)
            col += 1
            if col > 3:
                col = 0
                row += 1
    
    def button_click(self, char):
        if char == "=":
            try:
                result = eval(self.display.get())
                self.display.delete(0, tk.END)
                self.display.insert(0, str(result))
            except:
                self.display.delete(0, tk.END)
                self.display.insert(0, "Ошибка")
        elif char == "C":
            self.display.delete(0, tk.END)
        elif char == "√":
            try:
                result = math.sqrt(float(self.display.get()))
                self.display.delete(0, tk.END)
                self.display.insert(0, str(result))
            except:
                self.display.delete(0, tk.END)
                self.display.insert(0, "Ошибка")
        elif char == "x²":
            try:
                result = float(self.display.get()) ** 2
                self.display.delete(0, tk.END)
                self.display.insert(0, str(result))
            except:
                self.display.delete(0, tk.END)
                self.display.insert(0, "Ошибка")
        elif char == "sin":
            try:
                result = math.sin(math.radians(float(self.display.get())))
                self.display.delete(0, tk.END)
                self.display.insert(0, str(result))
            except:
                self.display.delete(0, tk.END)
                self.display.insert(0, "Ошибка")
        else:
            self.display.insert(tk.END, char)
    
    def run(self):
        self.root.mainloop()

if __name__ == "__main__":
    calc = SmartCalculator()
    calc.run()',
                'defense_text' => 'Мой калькулятор поддерживает все базовые операции, квадратные корни, возведение в квадрат, тригонометрические функции. Реализован ООП подход с классом SmartCalculator, что делает код масштабируемым. Интерфейс интуитивно понятен. Это отличный пример реального приложения!',
            ],
        ];
        
        foreach ($projects as $projectData) {
            $user = $users->random();
            $project = Project::create([
                'user_id' => $user->id,
                'title' => $projectData['title'],
                'description' => $projectData['description'],
                'code' => $projectData['code'],
                'defense_text' => $projectData['defense_text'],
                'code_score' => 85,
                'defense_score' => 75,
                'is_ai_generated' => false,
                'ai_generated_percentage' => 0,
                'complexity_level' => 7,
                'submitted_at' => now()->subDays(5),
                'evaluated_at' => now()->subDays(4),
            ]);
            
            $project->total_score = $project->calculateTotalScore();
            $rewards = $project->calculateRewards();
            $project->coins_reward = $rewards['coins'];
            $project->experience_points = $rewards['experience'];
            $project->is_approved = true;
            $project->save();
            
            $project->evaluation()->create([
                'code_metrics' => ['lines_of_code' => 80, 'functions_count' => 2, 'complexity_score' => 85],
                'defense_metrics' => ['text_length' => 250, 'quality_score' => 75],
                'ai_detection_results' => ['is_ai' => false, 'percentage' => 0, 'confidence' => 0.1],
                'feedback' => 'Отличный проект! Код хорошо структурирован.',
            ]);
        }
    }
}
