<?php

namespace App\Http\Requests;

use App\Models\Permohonan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermohonanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $permohonan = $this->route('permohonan');

        return $permohonan instanceof Permohonan
            ? ($this->user()?->can('update', $permohonan) ?? false)
            : ($this->user()?->isAdmin() ?? false);
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
            'kabupaten' => ['nullable', 'string', Rule::in(Permohonan::KABUPATEN_OPTIONS)],
            'permohonan' => ['nullable', 'string'],
        ];
    }
}
