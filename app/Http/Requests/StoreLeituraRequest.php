<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeituraRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'curso_id' => 'required',
            'sequencia' => ['required', Rule::unique('leituras', 'sequencia')->where('curso_id', $this->input('curso_id'))],
            'titulo' => 'required|min:10',
            'conteudo' => 'required:min:50'
        ];
    }
}
