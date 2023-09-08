<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Sequence;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::factory()
        ->times(25)
        ->state(new Sequence(
            ['status' => 1],
            ['status' => 0],
            ['status' => null],
        ))->create();
    }
}
