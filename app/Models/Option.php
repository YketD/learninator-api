<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int      $id
 * @property string   $value
 * @property int      $question_id
 * @property Question $question
 * @property bool     $is_correct
 * @property Carbon   $created_at
 * @property Carbon   $updated_at
 */
class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'value',
        'is_correct',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
