<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int          $id
 * @property string       $name
 * @property int          $question_type_id
 * @property QuestionType $questionType
 * @property int          $interest_id
 * @property Interest     $interest
 * @property bool         $is_required
 * @property bool         $is_multiple_choice
 * @property bool         $is_active
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 */
class Question extends Model
{
    use HasFactory;

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function interest()
    {
        return $this->belongsTo(Interest::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function optionsToString()
    {
        return implode(', ', $this->options()->pluck('value')->toArray());
    }
}
