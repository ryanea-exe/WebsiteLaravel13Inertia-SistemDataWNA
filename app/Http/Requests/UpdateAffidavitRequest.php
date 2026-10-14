<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Models\Affidavit;
use App\Services\DocumentStorageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAffidavitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $affidavit = $this->route('affidavit');

        return $affidavit instanceof Affidavit
            ? ($this->user()?->can('update', $affidavit) ?? false)
            : ($this->user()?->isAdmin() ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $affidavitId = $this->route('affidavit') instanceof Affidavit ? $this->route('affidavit')->id : $this->route('affidavit');

        return [
            'no_register' => ['required', 'string', 'max:80', Rule::unique('affidavit', 'no_register')->ignore($affidavitId)],
            'no_paspor' => ['required', 'string', 'max:80'],
            'nama' => ['required', 'string', 'max:180'],
            'tempat_lahir' => ['nullable', 'string', 'max:120'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', Rule::enum(Gender::class)],
            'kewarganegaraan' => ['required', 'string', 'max:100'],
            'masa_berlaku' => ['nullable', 'date'],
            'nama_ayah' => ['nullable', 'string', 'max:180'],
            'kewarganegaraan_ayah' => ['nullable', 'string', 'max:100'],
            'nama_ibu' => ['nullable', 'string', 'max:180'],
            'kewarganegaraan_ibu' => ['nullable', 'string', 'max:100'],
            'nomor_telepon' => ['nullable', 'string', 'max:40'],
            'alamat' => ['nullable', 'string'],
            'foto' => DocumentStorageService::photoRules(false),
            'remove_foto' => ['nullable', 'boolean'],
        ];
    }
}
