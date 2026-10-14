<?php

namespace App\Http\Requests;

use App\Models\RealisasiPnbp;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRealisasiPnbpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $record = $this->route('realisasi_pnbp');

        return $record instanceof RealisasiPnbp
            ? ($this->user()?->can('update', $record) ?? false)
            : ($this->user()?->isAdmin() ?? false);
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('bulan') && ! empty($this->bulan)) {
            try {
                $this->merge([
                    'bulan' => Carbon::parse($this->bulan)->startOfMonth()->toDateString(),
                ]);
            } catch (\Throwable) {
                // Let standard date validation handle invalid date strings
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $record = $this->route('realisasi_pnbp');
        $recordId = $record instanceof RealisasiPnbp ? $record->id : $record;

        return [
            'bulan' => [
                'required',
                'date',
                Rule::unique('realisasi_pnbp', 'bulan')->ignore($recordId),
            ],
            'target' => ['required', 'numeric', 'min:0'],
            'realisasi' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bulan.required' => 'Bulan realisasi wajib dipilih.',
            'bulan.date' => 'Format bulan tidak valid.',
            'bulan.unique' => 'Data realisasi PNBP untuk bulan tersebut sudah pernah dicatat.',
            'target.required' => 'Target penerimaan wajib diisi.',
            'target.numeric' => 'Target penerimaan harus berupa angka.',
            'target.min' => 'Target penerimaan tidak boleh bernilai negatif.',
            'realisasi.required' => 'Realisasi penerimaan wajib diisi.',
            'realisasi.numeric' => 'Realisasi penerimaan harus berupa angka.',
            'realisasi.min' => 'Realisasi penerimaan tidak boleh bernilai negatif.',
            'keterangan.max' => 'Keterangan tidak boleh melebihi 1000 karakter.',
        ];
    }
}
