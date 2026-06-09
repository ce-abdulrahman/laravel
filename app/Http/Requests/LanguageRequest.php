<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $languageId = $this->route('language');
        if (is_object($languageId)) {
            $languageId = $languageId->id;
        }

        return [
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique(Language::class)->ignore($languageId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'native_name' => ['required', 'string', 'max:100'],
            'direction' => ['required', 'in:ltr,rtl'],
            'flag' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_default' => $this->boolean('is_default'),
        ]);
    }
}
