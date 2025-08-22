<?php

namespace Database\Seeders;

use App\Models\ProfilingQuestion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfilingQuestionSeeder extends Seeder
{
    public function run(): void
    {
        ProfilingQuestion::create([
            'code' => 'gender',
            'text' => 'What is your gender?',
            'type' => 'single',
            'options' => ['Male', 'Female'],
            'is_active' => true,
        ]);

        ProfilingQuestion::create([
            'code' => 'dob',
            'text' => 'What is your date of birth?',
            'type' => 'date',
            'options' => null,
            'is_active' => true,
        ]);

        ProfilingQuestion::create([
            'code' => 'interests',
            'text' => 'What are your interests? (Select all that apply)',
            'type' => 'multiple',
            'options' => ['Sports', 'Music', 'Technology', 'Travel', 'Reading', 'Gaming', 'Cooking', 'Art'],
            'is_active' => true,
        ]);

        ProfilingQuestion::create([
            'code' => 'skills',
            'text' => 'What programming languages do you know?',
            'type' => 'multiple',
            'options' => ['JavaScript', 'Python', 'PHP', 'Java', 'C++', 'C#', 'Go', 'Rust', 'Swift'],
            'is_active' => true,
        ]);

        ProfilingQuestion::create([
            'code' => 'education',
            'text' => 'What is your highest level of education?',
            'type' => 'single',
            'options' => ['High School', 'Bachelor\'s Degree', 'Master\'s Degree', 'PhD', 'Other'],
            'is_active' => true,
        ]);
    }
}
