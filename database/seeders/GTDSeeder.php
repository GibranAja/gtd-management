<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Context;
use App\Models\Project;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;

class GTDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo user
        $user = User::create([
            'name' => 'GTD Demo User',
            'email' => 'demo@gtd.com',
            'password' => Hash::make('password'),
        ]);

        // Create default contexts
        $contexts = [
            ['name' => '@Office', 'icon' => '🏢', 'color' => '#3b82f6'],
            ['name' => '@Home', 'icon' => '🏠', 'color' => '#10b981'],
            ['name' => '@Phone', 'icon' => '📞', 'color' => '#f59e0b'],
            ['name' => '@Computer', 'icon' => '💻', 'color' => '#8b5cf6'],
            ['name' => '@Errands', 'icon' => '🚗', 'color' => '#ef4444'],
        ];

        foreach ($contexts as $contextData) {
            Context::create([
                'name' => $contextData['name'],
                'icon' => $contextData['icon'],
                'color' => $contextData['color'],
                'user_id' => $user->id,
            ]);
        }

        // Create sample project
        $project = Project::create([
            'title' => 'Website Redesign',
            'description' => 'Redesign company website with new branding',
            'status' => 'active',
            'due_date' => now()->addDays(30),
            'user_id' => $user->id,
        ]);

        // Create sample items
        $officeContext = Context::where('name', '@Office')->first();
        $computerContext = Context::where('name', '@Computer')->first();

        Item::create([
            'title' => 'Review project requirements',
            'description' => 'Go through the detailed requirements document',
            'type' => 'inbox',
            'user_id' => $user->id,
        ]);

        Item::create([
            'title' => 'Create wireframes',
            'description' => 'Design wireframes for main pages',
            'type' => 'next_action',
            'energy_level' => 3,
            'time_estimate' => 120,
            'project_id' => $project->id,
            'context_id' => $computerContext->id,
            'user_id' => $user->id,
        ]);

        Item::create([
            'title' => 'Approval from client',
            'description' => 'Waiting for client approval on design mockups',
            'type' => 'waiting_for',
            'waiting_for_person' => 'John Smith',
            'waiting_since' => now()->subDays(3),
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        Item::create([
            'title' => 'Learn new design framework',
            'description' => 'Maybe learn Tailwind CSS for future projects',
            'type' => 'someday_maybe',
            'user_id' => $user->id,
        ]);

        Item::create([
            'title' => 'Design guidelines document',
            'description' => 'Reference document for brand guidelines',
            'type' => 'reference',
            'user_id' => $user->id,
        ]);
    }
}
