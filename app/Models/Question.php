<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
