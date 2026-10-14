<?php

namespace App\Http\Requests;

use App\Models\LaporanIzinTinggal;
use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanIzinTinggalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', LaporanIzinTinggal::class) ?? false;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        $merges = [];
        foreach (['disetujui_l', 'disetujui_p', 'ditolak_l', 'ditolak_p'] as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $merges[$field] = 0;
            }
        }

        if (! empty($merges)) {
            $this->merge($merges);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'kategori' => ['required', 'string', 'max:120'],
            'layanan' => ['required', 'string', 'max:150'],
            'disetujui_l' => ['required', 'integer', 'min:0', 'max:1000000'],
            'disetujui_p' => ['required', 'integer', 'min:0', 'max:1000000'],
            'ditolak_l' => ['required', 'integer', 'min:0', 'max:1000000'],
            'ditolak_p' => ['required', 'integer', 'min:0', 'max:1000000'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
