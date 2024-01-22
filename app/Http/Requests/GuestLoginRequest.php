<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestLoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_id' => 'required|string',
            'name' => 'nullable|string',
            'accept_terms' => 'required|boolean|accepted',
            'accept_privacy' => 'required|boolean|accepted',
        ];
    }
}
