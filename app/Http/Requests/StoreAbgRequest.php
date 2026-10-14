<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Models\Abg;
use App\Services\DocumentStorageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbgRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Abg::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'no_register' => ['required', 'string', 'max:80', Rule::unique('abg', 'no_register')],
            'no_paspor' => ['required', 'string', 'max:80'],
            'nama' => ['required', 'string', 'max:180'],
            'tempat_lahir' => ['nullable', 'string', 'max:120'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', Rule::enum(Gender::class)],
            'kewarganegaraan' => ['required', 'string', 'max:100'],
            'nama_ayah' => ['nullable', 'string', 'max:180'],
            'kewarganegaraan_ayah' => ['nullable', 'string', 'max:100'],
            'nama_ibu' => ['nullable', 'string', 'max:180'],
            'kewarganegaraan_ibu' => ['nullable', 'string', 'max:100'],
            'nomor_telepon' => ['nullable', 'string', 'max:40'],
            'alamat' => ['nullable', 'string'],
            'foto' => DocumentStorageService::photoRules(false),
        ];
    }
}
