<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create sectors first since users belong to sectors
        $this->seedSectors();
        
        // Create users
        $this->seedUsers();
        
        // Create tags
        $this->seedTags();
        
        // Create reports - needs users and tags to exist first
        $this->seedReports();
    }

    private function seedSectors()
    {
        $sectors = [
            ['name' => 'IT & Software'],
            ['name' => 'Business'],
            ['name' => 'Finance & Accounting'],
            ['name' => 'Healthcare'],
            ['name' => 'Marketing'],
            ['name' => 'Engineering'],
            ['name' => 'Education'],
        ];

        foreach ($sectors as $sector) {
            Sector::create($sector);
        }
    }
    
    private function seedUsers()
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'sector_id' => Sector::inRandomOrder()->first()->id,
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'sector_id' => Sector::inRandomOrder()->first()->id,
        ]);
        
        // Create additional regular users
        User::factory(10)->create([
            'role' => 'user',
            'sector_id' => function () {
                return Sector::inRandomOrder()->first()->id;
            },
        ]);
    }
    
    private function seedTags()
    {
        $tags = [
            'php',
            'laravel',
            'javascript',
            'vue',
            'react',
            'angular',
            'database',
            'security',
            'machine learning',
            'ai',
            'web development',
            'mobile development',
            'devops',
            'cloud computing',
            'blockchain',
        ];
        
        foreach ($tags as $tagName) {
            Tag::create(['name' => $tagName]);
        }
    }
    
    private function seedReports()
    {
        // Generate 20 reports
        for ($i = 0; $i < 20; $i++) {
            $report = Report::create([
                'title' => fake()->sentence(),
                'description' => fake()->paragraph(3),
                'preview' => 'uploads/previews/default_preview.jpg', // Default preview path
                'url' => 'uploads/reports/default_report.pdf', // Default report path
                'validated' => fake()->boolean(70), // 70% chance to be validated
                'user_id' => User::inRandomOrder()->first()->id,
            ]);
            
            // Attach 2-4 random tags to each report
            $tagIds = Tag::inRandomOrder()->take(rand(2, 4))->pluck('id')->toArray();
            $report->tags()->sync($tagIds);
        }
        
        // // Ensure directories exist for the storage paths
        // if (!File::exists(public_path('storage/uploads/previews'))) {
        //     File::makeDirectory(public_path('storage/uploads/previews'), 0777, true);
        // }
        // if (!File::exists(public_path('storage/uploads/reports'))) {
        //     File::makeDirectory(public_path('storage/uploads/reports'), 0777, true);
        // }
        
        // // Create default placeholder files if they don't exist
        // if (!File::exists(public_path('storage/uploads/previews/default_preview.jpg'))) {
        //     File::copy(public_path('images/placeholder.jpg'), public_path('storage/uploads/previews/default_preview.jpg'));
        // }
        // if (!File::exists(public_path('storage/uploads/reports/default_report.pdf'))) {
        //     // Create an empty PDF file or copy from a template
        //     File::put(public_path('storage/uploads/reports/default_report.pdf'), '');
        // }
    }
}
