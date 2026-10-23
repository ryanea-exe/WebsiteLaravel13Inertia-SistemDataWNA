<?php

namespace App\Http\Requests;

use App\Models\LaporanIzinTinggal;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLaporanIzinTinggalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $report = $this->route('laporan_izin_tinggal');

        if ($report instanceof LaporanIzinTinggal) {
            return $this->user()?->can('update', $report) ?? false;
        }

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

        if ($this->has('bulan') && ! empty($this->bulan)) {
            try {
                $merges['bulan'] = Carbon::parse($this->bulan)->startOfMonth()->toDateString();
            } catch (\Throwable) {
                // Let standard date validation handle invalid date strings
            }
        } elseif ($this->has('bulan') && empty($this->bulan)) {
            $merges['bulan'] = null;
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
        $report = $this->route('laporan_izin_tinggal');
        $reportId = $report instanceof LaporanIzinTinggal ? $report->id : $report;

        return [
            'bulan' => ['required', 'date', Rule::unique('laporan_izin_tinggal', 'bulan')->ignore($reportId)],
            'kategori' => ['required', 'string', 'max:120'],
            'layanan' => ['required', 'string', 'max:150'],
            'disetujui_l' => ['required', 'integer', 'min:0', 'max:1000000'],
            'disetujui_p' => ['required', 'integer', 'min:0', 'max:1000000'],
            'ditolak_l' => ['required', 'integer', 'min:0', 'max:1000000'],
            'ditolak_p' => ['required', 'integer', 'min:0', 'max:1000000'],
            'keterangan' => ['nullable', 'string'],
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
            'bulan.required' => 'Bulan laporan wajib dipilih.',
            'bulan.date' => 'Format bulan laporan tidak valid.',
            'bulan.unique' => 'Data laporan izin tinggal untuk bulan tersebut sudah pernah dicatat.',
        ];
    }
}
