<?php

namespace App\Http\Controllers;

use App\Actions\GenerateQuestionAction;
use App\Http\Requests\CheckAnswerRequest;
use App\Http\Requests\QuestionRequest;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

    public function getQuestion(QuestionRequest $request, GenerateQuestionAction $generateQuestionAction)
    {
        $question = $generateQuestionAction->execute($request->user(), $request->question_type_id, $request->fresh);
        return response()->json($question->load('options'));
    }

    public function checkAnswer(CheckAnswerRequest $request)
    {
        $question = Question::query()->where('id', $request->id)->first();
        $option = $question->options()->where('id', $request->option_id)->first();

        Answer::query()->updateOrCreate([
            'user_id' => $request->user()->id,
            'question_id' => $request->id,
        ], [
            'option_id' => $option->id,
        ]);

        return response()->json(['is_correct' => $option->is_correct]);
    }
}
