<?php

namespace App\Http\Controllers;

use App\Actions\GenerateQuestionAction;
use App\Actions\LoadQuestionsAction;
use App\Http\Requests\CheckAnswerRequest;
use App\Http\Requests\QuestionRequest;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(QuestionRequest $request, LoadQuestionsAction $loadQuestionsAction)
    {
        $questions = $loadQuestionsAction->execute($request);

        return response()->json($questions);
    }

    public function getQuestion(QuestionRequest $request, GenerateQuestionAction $generateQuestionAction)
    {
        $question = $generateQuestionAction->execute(
            $request->user(),
            $request->input('question_type_id'),
            $request->input('interest_id'),
            $request->input('fresh')
        );

        return response()->json($question->load('options', 'interest'));
    }

    public function checkAnswer(CheckAnswerRequest $request)
    {
        $question = Question::query()->where('id', $request->id)->first();
        $option = $question->options()->where('id', $request->option_id)->first();

        if (!$option) {
            return response()->json(['error' => 'Option not found on question'], 404);
        }
        Answer::query()->updateOrCreate([
            'user_id'     => $request->user()->id,
            'question_id' => $request->id,
        ], [
            'option_id' => $option->id,
        ]);

        return response()->json(['is_correct' => $option->is_correct]);
    }
}
