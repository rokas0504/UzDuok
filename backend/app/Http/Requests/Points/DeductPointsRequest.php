<?php

namespace App\Http\Requests\Points;

use Illuminate\Foundation\Http\FormRequest;

class DeductPointsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $user->load('role');

        return $user && $user->isParent();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'child_id' => ['required', 'exists:users,id'],
            'points' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'child_id.required' => 'Vaikas yra privalomas',
            'child_id.exists' => 'Nurodytas vaikas nerastas',
            'points.required' => 'Taškai yra privalomi',
            'points.numeric' => 'Taškai turi būti skaičius',
            'points.min' => 'Taškai turi būti didesni nei 0',
            'reason.required' => 'Priežastis yra privaloma',
            'reason.max' => 'Priežastis negali būti ilgesnė nei 255 simboliai',
        ];
    }
}