<?php

namespace App\Actions;

use App\Models\Interest;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use OpenAI;

class GenerateQuestionAction
{

    public function execute(User $user, ?int $question_type_id = null, ?bool $fresh = false, ?int $interest_id = null)
    {
        if (!$question_type_id) {
            $question_type_id = random_int(1, 3);
        }

        switch ($question_type_id) {
            case 2:
                $question = $this->GenerateDefinitionGuesserQuestion($user, 2, $fresh, $interest_id);
                break;
            case 3:
                $question = $this->generateSwedishPuzzleQuestion($user, 3, $fresh, $interest_id);
                break;
            default:
                $question = $this->generateWordGuesserQuestion($user, 1, $fresh, $interest_id);
                break;
        }

        return $question;
    }

    private function generateWordGuesserQuestion(User $user, int $question_type_id, ?bool $fresh = false, ?int $interest_id = null)
    {
        if (!$fresh) {
            $question = Question::query()
                ->select('questions.*')
                ->leftJoin('answers', 'questions.id', '=', 'answers.question_id')
                ->leftJoin('options', 'answers.option_id', '=', 'options.id')
                ->where('questions.question_type_id', $question_type_id)
                ->where('questions.interest_id', $interest_id)
                ->inRandomOrder()
                ->where(function ($query) use ($user) {
                    $query->doesntHave('answers', 'or', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->where(function ($query) {
                                $query->whereNull('options.is_correct')
                                    ->orWhere('options.is_correct', '=', 0);
                            });
                    });
                })
                ->first();

            if ($question) {
                return $question;
            }
        }

        $openai = OpenAI::client(config('openai.api_key'), config('openai.organization'));

        if ($interest_id) {
            $interest = Interest::query()->where('id', $interest_id)->first();
            $prompt = 'Creëer een woordraadvraag voor een interesse in ' . $interest->name . ', met als mogelijke antwoorden 4 woorden';
        } elseif ($user->interests->count() > 0) {
            $interest = $user->interests->random();
            $prompt = 'Creëer een woordraadvraag voor een interesse in  ' . $interest->name . ', met als mogelijke antwoorden 4 woorden';
        }   else {
            $interest = Interest::query()->inRandomOrder()->first();
            $prompt = 'Creëer een woordraadvraag voor een interesse in ' . $interest->name . ', met als mogelijke antwoorden 4 woorden';
        }

        Interest::query()->where('id', $interest_id)->first();

        $gptResponse = $openai->chat()->create([
            'model' => config('openai.model'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'functions' => [
                [
                    'name' => 'createWordQuestionObject',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'question' => ['type' => 'string'],
                            'possible_answers' => ['type' => 'array', 'items' => ['type' => 'string']],
                            'correct_answer' => ['type' => 'integer']
                        ],
                        'required' => ['question', 'possible_answers', 'correct_answer']
                    ]
                ]
            ],
            'function_call' => ['name' => 'createWordQuestionObject']
        ]);

        $functionCall = $gptResponse['choices'][0]['message']['function_call'];
        $questionJson = json_decode($functionCall['arguments'], true);

        $question = new Question();
        $question->prompt = $questionJson['question'];
        $question->question_type_id = $question_type_id;
        $question->interest_id = $interest->id;
        $question->save();

        foreach ($questionJson['possible_answers'] as $key => $possible_answer) {
            $question->options()->create([
                'value' => $possible_answer,
                'is_correct' => $key === $questionJson['correct_answer']
            ]);
        }

        return $question;
    }

    private function GenerateDefinitionGuesserQuestion(User $user, int $question_type_id, ?bool $fresh, ?int $interest_id=null)
    {
        if (!$fresh) {
            $question = Question::query()
                ->select('questions.*')
                ->leftJoin('answers', 'questions.id', '=', 'answers.question_id')
                ->leftJoin('options', 'answers.option_id', '=', 'options.id')
                ->where('questions.question_type_id', $question_type_id)
                ->inRandomOrder()
                ->where(function ($query) use ($user) {
                    $query->doesntHave('answers', 'or', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->where(function ($query) {
                                $query->whereNull('options.is_correct')
                                    ->orWhere('options.is_correct', '=', 0);
                            });
                    });
                })
                ->first();

            if ($question) {
                return $question;
            }
        }

        if ($interest_id) {
            $interest = Interest::query()->where('id', $interest_id)->first();
        } elseif ($user->interests->count() > 0) {
            $interest = $user->interests->random();
        } else {
            $interest = Interest::query()->inRandomOrder()->first();
        }
        $prompt = 'Creëer als vraag property een lastig woord in de interessegroep ' . $interest->name . ' met als mogelijke antwoorden 4 definities';

        $openai = OpenAI::client(config('openai.api_key'), config('openai.organization'));

        $gptResponse = $openai->chat()->create([
            'model'         => config('openai.model'),
            'messages'      => [
                [
                    'role'    => 'user',
                    'content' => $prompt
                ]
            ],
            'functions'     => [
                [
                    'name'       => 'createDefinitionQuestionObject',
                    'parameters' => [
                        'type'       => 'object',
                        'properties' => [
                            'question'         => ['type' => 'string'],
                            'possible_answers' => ['type' => 'array', 'items' => ['type' => 'string']],
                            'correct_answer'   => ['type' => 'integer']
                        ],
                        'required'   => ['question', 'possible_answers', 'correct_answer']
                    ]
                ]
            ],
            'function_call' => ['name' => 'createDefinitionQuestionObject']
        ]);

        $functionCall = $gptResponse['choices'][0]['message']['function_call'];
        $questionJson = json_decode($functionCall['arguments'], true);

        $question = new Question();
        $question->prompt = $questionJson['question'];
        $question->question_type_id = 2;
        $question->interest_id = $interest->id;
        $question->save();

        foreach ($questionJson['possible_answers'] as $key => $possible_answer) {
            $question->options()->create([
                'value'      => $possible_answer,
                'is_correct' => $key === $questionJson['correct_answer']
            ]);
        }

        return $question;
    }

    private function generateSwedishPuzzleQuestion(User $user, int $int, ?bool $fresh, ?int $interest_id)
    {
        if (!$fresh) {
            $question = Question::query()
                ->select('questions.*')
                ->leftJoin('answers', 'questions.id', '=', 'answers.question_id')
                ->leftJoin('options', 'answers.option_id', '=', 'options.id')
                ->where('questions.question_type_id', $int)
                ->inRandomOrder()
                ->where(function ($query) use ($user) {
                    $query->doesntHave('answers', 'or', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->where(function ($query) {
                                $query->whereNull('options.is_correct')
                                    ->orWhere('options.is_correct', '=', 0);
                            });
                    });
                })
                ->first();

            if ($question) {
                return $question;
            }
        }

        if ($interest_id) {
            $interest = Interest::query()->where('id', $interest_id)->first();
        } elseif ($user->interests->count() > 0) {
            $interest = $user->interests->random();
        } else {
            $interest = Interest::query()->inRandomOrder()->first();
        }

        $prompt = "Genereer een uitdagend $interest->name -woord (7-11 tekens) met een korte hint (max 2 woorden) voor de gebruiker om te raden,
         zonder het volledige woord te onthullen.";

        $openai = OpenAI::client(config('openai.api_key'), config('openai.organization'));

        $gptResponse = $openai->chat()->create([
            'model'         => config('openai.model'),
            'messages'      => [
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'functions'     => [
                [
                    'name'       => 'createSwedishPuzzleQuestionObject',
                    'parameters' => [
                        'type'       => 'object',
                        'properties' => [
                            'hint' => ['type' => 'string'],
                            'volledig_woord'   => ['type' => 'string'],
                        ],
                        'required'   => ['volledig_woord', 'hint'],
                    ],
                ],
            ],
            'function_call' => ['name' => 'createSwedishPuzzleQuestionObject'],
        ]);

        $functionCall = $gptResponse['choices'][0]['message']['function_call'];
        $questionJson = json_decode($functionCall['arguments'], true);

        $question = new Question();
        $question->prompt = $this->verbergLetters($questionJson['volledig_woord']);
        $question->question_type_id = 3;
        $question->interest_id = $interest->id;
        $question->short_definition = $questionJson['hint'];
        $question->save();

        $question->options()->create([
            'value'      => $questionJson['volledig_woord'],
            'is_correct' => 1,
        ]);

        return $question;
    }

    function verbergLetters($woord) {
        $lengte = strlen($woord);
        $verborgenWoord = '';
        $minimaalAantalLetters = 2;
        $vervangenCount = 0;

        for ($i = 0; $i < $lengte; $i++) {
            // Bepaal willekeurig of een letter wordt vervangen door een underscore, behalve spaties
            if ($woord[$i] !== ' ' && ($vervangenCount < ($lengte - $minimaalAantalLetters)) && rand(0, 2)) {
                $verborgenWoord .= '_';
                $vervangenCount++;
            } else {
                $verborgenWoord .= $woord[$i];
            }
        }

        return $verborgenWoord;
    }
}
