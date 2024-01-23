<?php

namespace App\Http\Requests;

class QuestionRequest extends BasePaginateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'question_type_id' => 'nullable|exists:question_types,id',
            'fresh' => 'nullable|boolean',
            'retry' => 'nullable|boolean',
            'interest_id' => 'nullable|exists:interests,id',
        ]);
    }
}
