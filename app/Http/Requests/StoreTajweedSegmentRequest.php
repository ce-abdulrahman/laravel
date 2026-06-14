<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTajweedSegmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ayah_id' => 'required|exists:ayahs,id',
            'tajweed_rule_id' => 'required|exists:tajweed_rules,id',
            'matched_text' => 'required|string',
            'start_index' => 'nullable|integer|min:0',
            'end_index' => 'nullable|integer|min:0|gte:start_index',
            'metadata' => 'nullable|json',
            'note' => 'nullable|string',
        ];
    }
}
