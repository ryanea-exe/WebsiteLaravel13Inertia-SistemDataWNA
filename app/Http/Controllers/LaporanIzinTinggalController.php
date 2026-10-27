<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaporanIzinTinggalRequest;
use App\Http\Requests\UpdateLaporanIzinTinggalRequest;
use App\Models\LaporanIzinTinggal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LaporanIzinTinggalController extends Controller
{
    /**
     * Display a listing of Laporan Izin Tinggal records with filters, search, sorting, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', LaporanIzinTinggal::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:120'],
            'layanan' => ['nullable', 'string', 'max:150'],
            'sort_by' => ['nullable', 'string', Rule::in(['bulan', 'kategori', 'layanan', 'disetujui_l', 'disetujui_p', 'ditolak_l', 'ditolak_p', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $query = LaporanIzinTinggal::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['kategori'])) {
            $query->where('kategori', $validated['kategori']);
        }

        if (! empty($validated['layanan'])) {
            $query->where('layanan', $validated['layanan']);
        }

        // Calculate aggregate totals for the filtered results
        $summaryQuery = clone $query;
        $totalDisetujuiL = (int) $summaryQuery->sum('disetujui_l');
        $totalDisetujuiP = (int) $summaryQuery->sum('disetujui_p');
        $totalDitolakL = (int) $summaryQuery->sum('ditolak_l');
        $totalDitolakP = (int) $summaryQuery->sum('ditolak_p');

        $summary = [
            'total_disetujui_l' => $totalDisetujuiL,
            'total_disetujui_p' => $totalDisetujuiP,
            'total_ditolak_l' => $totalDitolakL,
            'total_ditolak_p' => $totalDitolakP,
            'grand_total_disetujui' => $totalDisetujuiL + $totalDisetujuiP,
            'grand_total_ditolak' => $totalDitolakL + $totalDitolakP,
            'grand_total' => $totalDisetujuiL + $totalDisetujuiP + $totalDitolakL + $totalDitolakP,
            'total_records' => (int) $summaryQuery->count(),
        ];

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 50);

        $query->orderBy($sortBy, $sortOrder);

        $laporan = $query->paginate($perPage)->withQueryString();

        $kategoriOptions = LaporanIzinTinggal::query()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $layananOptions = LaporanIzinTinggal::query()
            ->whereNotNull('layanan')
            ->where('layanan', '!=', '')
            ->distinct()
            ->orderBy('layanan')
            ->pluck('layanan');

        $user = $request->user();

        return Inertia::render('laporan-izin-tinggal/Index', [
            'laporan' => $laporan,
            'summary' => $summary,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'kategori' => $validated['kategori'] ?? '',
                'layanan' => $validated['layanan'] ?? '',
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'kategoriOptions' => $kategoriOptions,
            'layananOptions' => $layananOptions,
            'can' => [
                'create' => $user?->can('create', LaporanIzinTinggal::class) ?? false,
                'printList' => $user?->can('printList', LaporanIzinTinggal::class) ?? false,
            ],
        ]);
    }

    /**
     * Show the form for creating a new Laporan Izin Tinggal record.
     */
    public function create(): RedirectResponse
    {
        Gate::authorize('create', LaporanIzinTinggal::class);

        return redirect()->route('laporan-izin-tinggal.index');
    }

    /**
     * Store a newly created Laporan Izin Tinggal record in storage.
     */
    public function store(StoreLaporanIzinTinggalRequest $request): RedirectResponse
    {
        Gate::authorize('create', LaporanIzinTinggal::class);

        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            LaporanIzinTinggal::create($data);
        });

        return redirect()->route('laporan-izin-tinggal.index')->with('success', 'Data laporan izin tinggal berhasil ditambahkan.');
    }

    /**
     * Display the specified Laporan Izin Tinggal record.
     */
    public function show(Request $request, LaporanIzinTinggal $laporan_izin_tinggal): Response|JsonResponse
    {
        Gate::authorize('view', $laporan_izin_tinggal);

        if ($request->wantsJson()) {
            return response()->json(['laporan' => $laporan_izin_tinggal]);
        }

        return Inertia::render('laporan-izin-tinggal/Show', [
            'laporan' => $laporan_izin_tinggal,
        ]);
    }

    /**
     * Show the form for editing the specified Laporan Izin Tinggal record.
     */
    public function edit(LaporanIzinTinggal $laporan_izin_tinggal): RedirectResponse
    {
        Gate::authorize('update', $laporan_izin_tinggal);

        return redirect()->route('laporan-izin-tinggal.index');
    }

    /**
     * Update the specified Laporan Izin Tinggal record in storage.
     */
    public function update(UpdateLaporanIzinTinggalRequest $request, LaporanIzinTinggal $laporan_izin_tinggal): RedirectResponse
    {
        Gate::authorize('update', $laporan_izin_tinggal);

        $data = $request->validated();

        DB::transaction(function () use ($laporan_izin_tinggal, $data): void {
            $laporan_izin_tinggal->update($data);
        });

        return redirect()->route('laporan-izin-tinggal.index')->with('success', 'Data laporan izin tinggal berhasil diperbarui.');
    }

    /**
     * Remove the specified Laporan Izin Tinggal record from storage.
     */
    public function destroy(Request $request, LaporanIzinTinggal $laporan_izin_tinggal): RedirectResponse
    {
        Gate::authorize('delete', $laporan_izin_tinggal);

        DB::transaction(function () use ($laporan_izin_tinggal): void {
            $laporan_izin_tinggal->delete();
        });

        return redirect()->route('laporan-izin-tinggal.index')->with('success', 'Data laporan izin tinggal berhasil dihapus.');
    }

    /**
     * Display printable list of Laporan Izin Tinggal records for browser PDF print.
     */
    public function printList(Request $request): Response
    {
        Gate::authorize('printList', LaporanIzinTinggal::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:120'],
            'layanan' => ['nullable', 'string', 'max:150'],
        ]);

        $query = LaporanIzinTinggal::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['kategori'])) {
            $query->where('kategori', $validated['kategori']);
        }

        if (! empty($validated['layanan'])) {
            $query->where('layanan', $validated['layanan']);
        }

        $laporanList = $query->orderBy('kategori', 'asc')->orderBy('layanan', 'asc')->get();

        // Calculate aggregate totals for the printable report
        $totalDisetujuiL = $laporanList->sum('disetujui_l');
        $totalDisetujuiP = $laporanList->sum('disetujui_p');
        $totalDitolakL = $laporanList->sum('ditolak_l');
        $totalDitolakP = $laporanList->sum('ditolak_p');

        $summary = [
            'total_disetujui_l' => $totalDisetujuiL,
            'total_disetujui_p' => $totalDisetujuiP,
            'total_ditolak_l' => $totalDitolakL,
            'total_ditolak_p' => $totalDitolakP,
            'grand_total_disetujui' => $totalDisetujuiL + $totalDisetujuiP,
            'grand_total_ditolak' => $totalDitolakL + $totalDitolakP,
            'grand_total' => $totalDisetujuiL + $totalDisetujuiP + $totalDitolakL + $totalDitolakP,
            'total_records' => $laporanList->count(),
        ];

        return Inertia::render('laporan-izin-tinggal/PrintList', [
            'laporanList' => $laporanList,
            'summary' => $summary,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'kategori' => $validated['kategori'] ?? '',
                'layanan' => $validated['layanan'] ?? '',
            ],
            'printedAt' => now()->translatedFormat('d F Y H:i'),
        ]);
    }

    /**
     * Backwards-compatible alias for printList.
     */
    public function exportPdf(Request $request): Response
    {
        return $this->printList($request);
    }
}
