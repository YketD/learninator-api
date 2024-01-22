<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int                   $id
 * @property string                $name
 * @property Collection|Question[] $questions
 * @property Collection|Option[]   $options
 * @property Collection|Answer[]   $answers
 * @property Carbon                $created_at
 * @property Carbon                $updated_at
 */
class QuestionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
