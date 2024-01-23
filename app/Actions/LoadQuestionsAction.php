<?php

namespace App\Actions;

use App\Http\Requests\QuestionRequest;
use App\Models\Question;

class LoadQuestionsAction
{
    public function execute(QuestionRequest $request)
    {
        return Question::query()
            ->when($request->input('question_type_id'), function ($query, $question_type_id) {
                $query->where('question_type_id', $question_type_id);
            })
            ->when(!$request->input('retry', true), function ($query, $fresh) {
                $query->where(function ($query) {
                    $query->whereDoesntHave('answers')
                        ->orWhereHas('answers', function ($query) {
                            $query->whereHas('options.is_correct', false);
                        });
                });
            })->when($request->input('interest_id'), function ($query, $interest_id) {
                $query->where('interest_id', $interest_id);
            })
            ->with(['options', 'interest'])
            ->paginate($request->input('per_page', 10), ['*'], 'page', $request->input('page', 1));
    }
}
