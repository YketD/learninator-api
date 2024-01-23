<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Models\GameSession
 *
 * @property int                   $id
 * @property int                   $user_id
 * @property int                   $question_count
 * @property int                   $correct_count
 * @property int                   $score
 * @property string                $game_session_id
 * @property bool                  $is_complete
 * @property Collection|Question[] $questions
 * @property Carbon                $start
 * @property Carbon|null           $end
 * @property Carbon                $created_at
 * @property Carbon                $updated_at
 */
class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_count',
        'correct_count',
        'score',
        'game_session_id',
        'is_complete',
        'start',
        'end',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'start'       => 'datetime',
        'end'         => 'datetime',
    ];

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }
}
