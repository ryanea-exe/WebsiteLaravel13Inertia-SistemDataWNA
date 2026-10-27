<?php

namespace App\Http\Controllers;

use App\Enums\JenisIzinTinggal;
use App\Models\Wna;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EarlyWarningController extends Controller
{
    /**
     * Display a listing of WNA within the early warning period with categorization, filters, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewEarlyWarning', Wna::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', Rule::in(['semua', 'merah', 'kuning', 'biru', 'kedaluwarsa'])],
            'jenis_izin_tinggal' => ['nullable', 'string', Rule::enum(JenisIzinTinggal::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', Rule::in(['masa_berlaku', 'nama', 'no_paspor', 'no_register', 'jenis_izin_tinggal', 'kewarganegaraan', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $referenceDate = Carbon::now('Asia/Jakarta')->startOfDay();
        $kategori = $validated['kategori'] ?? 'semua';
        $query = Wna::query()->kategoriPeringatan($kategori, $referenceDate);

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_izin_tinggal'])) {
            $query->where('jenis_izin_tinggal', $validated['jenis_izin_tinggal']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        $sortBy = $validated['sort_by'] ?? 'masa_berlaku';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $perPage = (int) ($validated['per_page'] ?? 50);

        $query->orderBy($sortBy, $sortOrder);

        $wna = $query->paginate($perPage)->withQueryString();

        $counts = Wna::getEarlyWarningCounts($referenceDate);

        $kewarganegaraanOptions = Wna::query()
            ->whereNotNull('kewarganegaraan')
            ->distinct()
            ->orderBy('kewarganegaraan')
            ->pluck('kewarganegaraan');

        return Inertia::render('early-warning/Index', [
            'wna' => $wna,
            'counts' => $counts,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'kategori' => $kategori,
                'jenis_izin_tinggal' => $validated['jenis_izin_tinggal'] ?? '',
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? '',
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'kewarganegaraanOptions' => $kewarganegaraanOptions,
            'jenisIzinTinggalOptions' => array_map(
                fn (JenisIzinTinggal $case) => ['value' => $case->value, 'label' => $case->label()],
                JenisIzinTinggal::cases()
            ),
        ]);
    }
}
