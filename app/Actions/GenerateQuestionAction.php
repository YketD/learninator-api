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
        switch ($question_type_id) {
            case 1:
                $question = $this->generateWordGuesserQuestion($user, 1, $fresh, $interest_id);
                break;
            case 2:
                $question = $this->GenerateDefinitionGuesserQuestion($user, 2, $fresh, $interest_id);
                break;
            case 3:
                $question = $this->generateSwedishPuzzleQuestion($user, 3, $fresh, $interest_id);
                break;
            default:
                $question = $this->generateRandomQuestion($user, $fresh);
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
            $prompt = 'Creëer een woordraadvraag voor een interesse in ' . $interest->name;
        } elseif ($user->interests->count() > 0) {
            $interest = $user->interests->random();
            $prompt = 'Creëer een woordraadvraag voor een interesse in  ' . $interest->name;
        }   else {
            $interest = Interest::query()->inRandomOrder()->first();
            $prompt = 'Creëer een woordraadvraag voor een interesse in ' . $interest->name;
        }

        Interest::query()->where('id', $interest_id)->first();

        $gptResponse = $openai->chat()->create([
            'model' => 'gpt-3.5-turbo-1106',
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
            'model'         => 'gpt-3.5-turbo-1106',
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

        $prompt = "Genereer alsjeblieft een woord van 8 tot 10 tekens binnen het interessegebied $interest->name.
        bedenk zelf een korte definitie of hint voor het woord, maximaal 3 woorden. 
        Het doel is dat de gebruiker het ontbrekende deel van het woord invult op basis van de gegenereerde hint. 
        Zorg ervoor dat het woord uitdagend is voor het trainen van vocabulaire binnen het specifieke interessegebied.";

        // $prompt = 'Creëer een Zweedse puzzelwoord voor een interesse in ' . $interest->name . '.
        // De "question" moet het nederlandse woord zijn, met ' . rand(8, 10) . ' letters,
        // waarvan er maximaal 4 letters zichtbaar mogen zijn,
        // en de rest vervangen door _.
        // de "short_definition" moet een hint zijn van de betekenis van het woord & mag het woord niet bevatten.';

        $openai = OpenAI::client(config('openai.api_key'), config('openai.organization'));

        $gptResponse = $openai->chat()->create([
            'model'         => 'gpt-3.5-turbo-1106',
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
                            'short_definition' => ['type' => 'string'],
                            'volledig_woord'   => ['type' => 'string'],
                        ],
                        'required'   => ['volledig_woord', 'short_definition'],
                    ],
                ],
            ],
            'function_call' => ['name' => 'createSwedishPuzzleQuestionObject'],
        ]);

        $functionCall = $gptResponse['choices'][0]['message']['function_call'];
        $questionJson = json_decode($functionCall['arguments'], true);
        Log::info($questionJson);
        $question = new Question();
        $question->prompt = $this->verbergLetters($questionJson['volledig_woord']);
        $question->question_type_id = 3;
        $question->interest_id = $interest->id;
        $question->short_definition = $questionJson['short_definition'];
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

        for ($i = 0; $i < $lengte; $i++) {
            // Vervang letters door underscores, behalve spaties
            if ($woord[$i] !== ' ') {
                $verborgenWoord .= '_';
            } else {
                $verborgenWoord .= ' ';
            }
        }

        return $verborgenWoord;
    }

}
