<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\JenisIzinTinggal;
use App\Models\Wna;
use App\Services\DocumentStorageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWnaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Wna::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'no_register' => ['required', 'string', 'max:80', Rule::unique('wna', 'no_register')],
            'no_paspor' => ['required', 'string', 'max:80'],
            'nama' => ['required', 'string', 'max:180'],
            'jenis_kelamin' => ['required', Rule::enum(Gender::class)],
            'kewarganegaraan' => ['required', 'string', 'max:100'],
            'jenis_izin_tinggal' => ['required', Rule::enum(JenisIzinTinggal::class)],
            'izin_tinggal_ke' => ['nullable', 'string', 'max:50'],
            'masa_berlaku' => ['required', 'date'],
            'penjamin' => ['nullable', 'string', 'max:180'],
            'alamat_penjamin' => ['nullable', 'string'],
            'nomor_telepon' => ['nullable', 'string', 'max:40'],
            'foto' => DocumentStorageService::photoRules(false),
        ];
    }
}
