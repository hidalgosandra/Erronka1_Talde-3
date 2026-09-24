<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::firstOrCreate(['title' => 'Curso de ejemplo: introducción a la programación'], [
            'description' => 'Curso de demostración para probar las inscripciones. Practica variables, condiciones y bucles.',
        ]);
    }
}
