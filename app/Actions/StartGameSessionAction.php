<?php

namespace App\Actions;

use App\Http\Requests\QuestionRequest;
use App\Models\GameSession;

class StartGameSessionAction
{
    public function execute(QuestionRequest $request)
    {
        return GameSession::query()->firstOrCreate([
            'user_id'         => $request->user()->id,
            'game_session_id' => $request->input('game_session_id'),
        ], [
            'is_complete'    => false,
            'question_count' => $request->input('per_page', 10),
            'start'          => now(),
        ]);
    }
}
