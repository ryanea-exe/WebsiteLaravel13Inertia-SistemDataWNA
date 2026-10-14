<?php

namespace App\Http\Requests;

use App\Models\Penerbitan;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePenerbitanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $penerbitan = $this->route('penerbitan');

        return $penerbitan instanceof Penerbitan
            ? ($this->user()?->can('update', $penerbitan) ?? false)
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
            'kabupaten' => ['nullable', 'string', 'max:120'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
