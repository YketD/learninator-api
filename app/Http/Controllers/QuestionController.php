<?php

namespace App\Http\Controllers;

use App\Actions\GenerateQuestionAction;
use App\Http\Requests\QuestionRequest;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

    public function getQuestion(QuestionRequest $request, GenerateQuestionAction $generateQuestionAction)
    {
        $question = $generateQuestionAction->execute($request->user(), $request->question_type_id, $request->fresh);
        return response()->json($question->load('options'));

        $question = Question::query()->where('id', $request->id)->first();
        return response()->json($question);
    }
}
