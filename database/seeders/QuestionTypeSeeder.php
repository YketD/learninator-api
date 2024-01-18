<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wordGuessingQuestionTypes = [
            [
                'id' => 1,
                'name' => 'Raad het woord',
                'description' => 'Ontvang een definitie, op basis van de definitie moet je het woord raden.',
            ],
            [
                'id' => 2,
                'name' => 'Raad de definitie',
                'description' => 'Ontvang een woord, op basis van het woord moet je de juiste definitie raden.',
            ],
            [
                'id' => 3,
                'name' => 'Zweeds raadsel',
                'description' => 'Ontvang een gedeelte van een woord en een korte definitie, Raadt het volledige woord.',
            ],
        ];

        foreach ($wordGuessingQuestionTypes as $wordGuessingQuestionType) {
            \App\Models\QuestionType::query()->create($wordGuessingQuestionType);
        }
    }
}
