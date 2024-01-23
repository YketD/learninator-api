<?php

namespace App\Actions;

use App\Http\Requests\QuestionRequest;
use App\Models\GameSession;
use App\Models\Question;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;

class LoadQuestionsAction
{
    public function execute(QuestionRequest $request, GameSession $gameSession = null)
    {
        if ($gameSession && !$gameSession->is_complete && $gameSession->questions()->count() > 0) {
            return $gameSession
                ->load([
                    'questions',
                    'questions.options',
                    'questions.interest',
                    'questions.answers' => function ($query) {
                        $query->where('user_id', '=', 1);
                    }
                ]);
        }

        $questionsQuery = Question::query()
            ->when($request->input('question_type_id'), function ($query) use ($request) {
                $query->where('question_type_id', $request->input('question_type_id'));
            })
            ->when($request->input('interest_id'), function ($query) use ($request) {
                $query->where('interest_id', $request->input('interest_id'));
            })->where(function ($query) {
                $query->whereDoesntHave('answers')
                    ->orWhereHas('answers', function ($query) {
                        $query->where('answers.user_id', '<>', Auth::user()->id)
                            ->whereHas('option', function ($query) {
                                $query->where('is_correct', false);
                            });
                    })
                   ;
            }) ->with(['options', 'interest']);

        $questions = $questionsQuery->paginate($request->input('per_page', 10), ['*'], 'page', $request->input('page', 1));

        $questionCollection = $questions->getCollection();

        $questionCollection->each(function ($question) use ($gameSession) {
            if ($gameSession) {
                $gameSession->questions()->attach($question->id);
            }
        });

        if ($gameSession) {
            $gameSession->question_count = $questionCollection->count();
            $gameSession->save();

            return $gameSession->load([
                'questions',
                'questions.options',
                'questions.interest',
                'questions.answers' => function ($query) {
                    $query->where('user_id', '=', 1);
                }
            ]);
        }

        return $questions;
    }
}
