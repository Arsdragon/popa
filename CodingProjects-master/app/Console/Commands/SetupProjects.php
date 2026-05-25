<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupProjects extends Command
{
    protected $signature = 'projects:setup';
    protected $description = 'Setup Big Boys Projects system';

    public function handle()
    {
        $this->info('Setting up Big Boys Projects system...');
        
        // Run migrations
        $this->call('migrate', ['--path' => 'database/migrations/2026_05_22_151808_create_big_boys_projects_table.php']);
        
        // Run seeder
        $this->call('db:seed', ['--class' => 'Database\Seeds\ProjectsSeeder']);
        
        $this->info('Big Boys Projects system setup completed!');
        $this->info('Access at: /insider/projects');
    }
}
