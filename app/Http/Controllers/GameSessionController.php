<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class GameSessionController extends Controller
{
    public function index()
    {
        return response()->json(
            GameSession::query()
                ->where('user_id', auth()->id())
                ->get());
    }

    public function show(GameSession $gameSession)
    {
        return response()->json(
            $gameSession->load([
                'questions',
                'questions.options',
                'questions.interest',
                'questions.answers' => function ($query) {
                    $query->where('user_id', '=', 1);
                }
            ]));
    }
}
