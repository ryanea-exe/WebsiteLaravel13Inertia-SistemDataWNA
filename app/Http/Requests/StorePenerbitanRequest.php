<?php

namespace App\Http\Requests;

use App\Models\Penerbitan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePenerbitanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Penerbitan::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'no_paspor' => ['nullable', 'string', 'max:80'],
            'nama' => ['required', 'string', 'max:180'],
            'kewarganegaraan' => ['required', 'string', 'max:100'],
            'layanan' => ['required', 'string', 'max:150'],
            'tanggal' => ['required', 'date'],
            'kabupaten' => ['nullable', 'string', Rule::in(Penerbitan::KABUPATEN_OPTIONS)],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
