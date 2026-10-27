<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRealisasiPnbpRequest;
use App\Http\Requests\UpdateRealisasiPnbpRequest;
use App\Models\RealisasiPnbp;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RealisasiPnbpController extends Controller
{
    /**
     * Display a listing of Realisasi PNBP records with search, filter, sort, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', RealisasiPnbp::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tahun' => ['nullable', 'string', 'regex:/^[0-9]{4}$/'],
            'sort_by' => ['nullable', 'string', Rule::in(['bulan', 'target', 'realisasi', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $query = RealisasiPnbp::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['tahun'])) {
            $query->tahun($validated['tahun']);
        }

        $sortBy = $validated['sort_by'] ?? 'bulan';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 50);

        $query->orderBy($sortBy, $sortOrder);

        $realisasiPnbp = $query->paginate($perPage)->withQueryString();

        // Calculate summary metrics for the current filtered query
        $summaryQuery = RealisasiPnbp::query();
        if (! empty($validated['search'])) {
            $summaryQuery->search($validated['search']);
        }
        if (! empty($validated['tahun'])) {
            $summaryQuery->tahun($validated['tahun']);
        }

        $allMatching = $summaryQuery->get();
        $totalTarget = (float) $allMatching->sum(fn (RealisasiPnbp $item) => (float) $item->target);
        $totalRealisasi = (float) $allMatching->sum(fn (RealisasiPnbp $item) => (float) $item->realisasi);
        $totalSelisih = round($totalRealisasi - $totalTarget, 2);
        $persentaseTotal = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 2) : 0.0;

        // Distinct years list for filter dropdown
        $tahunOptions = RealisasiPnbp::query()
            ->pluck('bulan')
            ->map(fn ($date) => Carbon::parse($date)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $user = $request->user();

        return Inertia::render('realisasi-pnbp/Index', [
            'realisasiPnbp' => $realisasiPnbp,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'tahun' => $validated['tahun'] ?? '',
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'tahunOptions' => $tahunOptions,
            'summary' => [
                'total_target' => $totalTarget,
                'total_realisasi' => $totalRealisasi,
                'total_selisih' => $totalSelisih,
                'persentase_total' => $persentaseTotal,
            ],
            'can' => [
                'create' => $user?->can('create', RealisasiPnbp::class) ?? false,
                'printList' => $user?->can('printList', RealisasiPnbp::class) ?? false,
            ],
        ]);
    }

    /**
     * Show the form for creating a new Realisasi PNBP record.
     */
    public function create(): RedirectResponse
    {
        Gate::authorize('create', RealisasiPnbp::class);

        return redirect()->route('realisasi-pnbp.index');
    }

    /**
     * Store a newly created Realisasi PNBP record in storage.
     */
    public function store(StoreRealisasiPnbpRequest $request): RedirectResponse
    {
        Gate::authorize('create', RealisasiPnbp::class);

        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            RealisasiPnbp::create($data);
        });

        return redirect()->route('realisasi-pnbp.index')->with('success', 'Data realisasi PNBP berhasil ditambahkan.');
    }

    /**
     * Display the specified Realisasi PNBP record.
     */
    public function show(Request $request, RealisasiPnbp $realisasiPnbp): Response|JsonResponse
    {
        Gate::authorize('view', $realisasiPnbp);

        if ($request->wantsJson()) {
            return response()->json(['realisasiPnbp' => $realisasiPnbp]);
        }

        return Inertia::render('realisasi-pnbp/Show', [
            'realisasiPnbp' => $realisasiPnbp,
        ]);
    }

    /**
     * Show the form for editing the specified Realisasi PNBP record.
     */
    public function edit(RealisasiPnbp $realisasiPnbp): RedirectResponse
    {
        Gate::authorize('update', $realisasiPnbp);

        return redirect()->route('realisasi-pnbp.index');
    }

    /**
     * Update the specified Realisasi PNBP record in storage.
     */
    public function update(UpdateRealisasiPnbpRequest $request, RealisasiPnbp $realisasiPnbp): RedirectResponse
    {
        Gate::authorize('update', $realisasiPnbp);

        $data = $request->validated();

        DB::transaction(function () use ($realisasiPnbp, $data): void {
            $realisasiPnbp->update($data);
        });

        return redirect()->route('realisasi-pnbp.index')->with('success', 'Data realisasi PNBP berhasil diperbarui.');
    }

    /**
     * Remove the specified Realisasi PNBP record from storage.
     */
    public function destroy(Request $request, RealisasiPnbp $realisasiPnbp): RedirectResponse
    {
        Gate::authorize('delete', $realisasiPnbp);

        DB::transaction(function () use ($realisasiPnbp): void {
            $realisasiPnbp->delete();
        });

        return redirect()->route('realisasi-pnbp.index')->with('success', 'Data realisasi PNBP berhasil dihapus.');
    }

    /**
     * Display printable list of Realisasi PNBP records for browser PDF print.
     */
    public function printList(Request $request): Response
    {
        Gate::authorize('printList', RealisasiPnbp::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tahun' => ['nullable', 'string', 'regex:/^[0-9]{4}$/'],
        ]);

        $query = RealisasiPnbp::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['tahun'])) {
            $query->tahun($validated['tahun']);
        }

        $realisasiList = $query->orderBy('bulan', 'desc')->get();

        $totalTarget = (float) $realisasiList->sum(fn (RealisasiPnbp $item) => (float) $item->target);
        $totalRealisasi = (float) $realisasiList->sum(fn (RealisasiPnbp $item) => (float) $item->realisasi);
        $totalSelisih = round($totalRealisasi - $totalTarget, 2);
        $persentaseTotal = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 2) : 0.0;

        return Inertia::render('realisasi-pnbp/PrintList', [
            'realisasiList' => $realisasiList,
            'summary' => [
                'total_target' => $totalTarget,
                'total_realisasi' => $totalRealisasi,
                'total_selisih' => $totalSelisih,
                'persentase_total' => $persentaseTotal,
            ],
            'filters' => [
                'search' => $validated['search'] ?? '',
                'tahun' => $validated['tahun'] ?? '',
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
