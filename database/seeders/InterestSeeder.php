<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InterestSeeder extends Seeder
{
    public function run()
    {
        $interests = [
            "Dieren",
            "Wetenschap",
            "Geschiedenis",
            "Kunst",
            "Technologie",
            "Natuur",
            "Sport",
            "Muziek",
            "Film",
            "Reizen",
            "Mode",
            "Voedsel",
            "Gezondheid",
            "Literatuur",
            "Taalkunde",
            "Psychologie",
            "Astrologie",
            "Fotografie",
            "Filosofie",
            "Architectuur",
            "Economie",
            "Politiek",
            "Wetenschappelijke Fictie",
            "Computerprogrammering",
            "Hobby's",
        ];

        foreach ($interests as $interest) {
            \App\Models\Interest::query()->firstOrCreate([
                'name' => $interest,
            ]);
        }
    }
}
