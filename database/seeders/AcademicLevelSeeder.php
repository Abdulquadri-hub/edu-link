<?php

namespace Database\Seeders;

use App\Models\AcademicLevel;
use Illuminate\Database\Seeder;

class AcademicLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            // Elementary (Grades 1-7)
            ['name' => 'Grade 1', 'grade_number' => 1, 'level_type' => 'elementary', 'sort_order' => 1, 'description' => 'First year of elementary education'],
            ['name' => 'Grade 2', 'grade_number' => 2, 'level_type' => 'elementary', 'sort_order' => 2, 'description' => 'Second year of elementary education'],
            ['name' => 'Grade 3', 'grade_number' => 3, 'level_type' => 'elementary', 'sort_order' => 3, 'description' => 'Third year of elementary education'],
            ['name' => 'Grade 4', 'grade_number' => 4, 'level_type' => 'elementary', 'sort_order' => 4, 'description' => 'Fourth year of elementary education'],
            ['name' => 'Grade 5', 'grade_number' => 5, 'level_type' => 'elementary', 'sort_order' => 5, 'description' => 'Fifth year of elementary education'],
            ['name' => 'Grade 6', 'grade_number' => 6, 'level_type' => 'elementary', 'sort_order' => 6, 'description' => 'Sixth year of elementary education'],
            ['name' => 'Grade 7', 'grade_number' => 7, 'level_type' => 'elementary', 'sort_order' => 7, 'description' => 'Seventh year of elementary education'],
            
            // Middle School (Grades 8-10)
            ['name' => 'Grade 8', 'grade_number' => 8, 'level_type' => 'middle', 'sort_order' => 8, 'description' => 'First year of middle school'],
            ['name' => 'Grade 9', 'grade_number' => 9, 'level_type' => 'middle', 'sort_order' => 9, 'description' => 'Second year of middle school'],
            ['name' => 'Grade 10', 'grade_number' => 10, 'level_type' => 'middle', 'sort_order' => 10, 'description' => 'Third year of middle school'],
            
            // High School (Grades 11-12)
            ['name' => 'Grade 11', 'grade_number' => 11, 'level_type' => 'high', 'sort_order' => 11, 'description' => 'First year of high school'],
            ['name' => 'Grade 12', 'grade_number' => 12, 'level_type' => 'high', 'sort_order' => 12, 'description' => 'Second year of high school - Final year'],
        ];

        foreach ($levels as $level) {
            AcademicLevel::create($level);
        }

        $this->command->info('Academic levels seeded successfully!');
    }
}