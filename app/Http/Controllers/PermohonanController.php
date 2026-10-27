<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermohonanRequest;
use App\Http\Requests\UpdatePermohonanRequest;
use App\Models\Permohonan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PermohonanController extends Controller
{
    /**
     * Display a listing of Permohonan records with search, filter, sort, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Permohonan::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'layanan' => ['nullable', 'string', 'max:150'],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:120'],
            'sort_by' => ['nullable', 'string', Rule::in(['tanggal', 'nama', 'no_paspor', 'layanan', 'kewarganegaraan', 'kabupaten', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $query = Permohonan::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['layanan'])) {
            $query->where('layanan', $validated['layanan']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        if (! empty($validated['kabupaten'])) {
            $query->where('kabupaten', $validated['kabupaten']);
        }

        $sortBy = $validated['sort_by'] ?? 'tanggal';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 50);

        $query->orderBy($sortBy, $sortOrder);

        $permohonan = $query->paginate($perPage)->withQueryString();

        $layananOptions = Permohonan::query()
            ->whereNotNull('layanan')
            ->where('layanan', '!=', '')
            ->distinct()
            ->orderBy('layanan')
            ->pluck('layanan');

        $kewarganegaraanOptions = Permohonan::query()
            ->whereNotNull('kewarganegaraan')
            ->where('kewarganegaraan', '!=', '')
            ->distinct()
            ->orderBy('kewarganegaraan')
            ->pluck('kewarganegaraan');

        $kabupatenOptions = Permohonan::query()
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->distinct()
            ->orderBy('kabupaten')
            ->pluck('kabupaten');

        $user = $request->user();

        return Inertia::render('permohonan/Index', [
            'permohonan' => $permohonan,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'layanan' => $validated['layanan'] ?? '',
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? '',
                'kabupaten' => $validated['kabupaten'] ?? '',
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'layananOptions' => $layananOptions,
            'kewarganegaraanOptions' => $kewarganegaraanOptions,
            'kabupatenOptions' => $kabupatenOptions,
            'can' => [
                'create' => $user?->can('create', Permohonan::class) ?? false,
                'printList' => $user?->can('printList', Permohonan::class) ?? false,
            ],
        ]);
    }

    /**
     * Show the form for creating a new Permohonan record.
     */
    public function create(): RedirectResponse
    {
        Gate::authorize('create', Permohonan::class);

        return redirect()->route('permohonan.index');
    }

    /**
     * Store a newly created Permohonan record in storage.
     */
    public function store(StorePermohonanRequest $request): RedirectResponse
    {
        Gate::authorize('create', Permohonan::class);

        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            Permohonan::create($data);
        });

        return redirect()->route('permohonan.index')->with('success', 'Data permohonan keimigrasian berhasil ditambahkan.');
    }

    /**
     * Display the specified Permohonan record.
     */
    public function show(Request $request, Permohonan $permohonan): Response|JsonResponse
    {
        Gate::authorize('view', $permohonan);

        if ($request->wantsJson()) {
            return response()->json(['permohonan' => $permohonan]);
        }

        return Inertia::render('permohonan/Show', [
            'permohonan' => $permohonan,
        ]);
    }

    /**
     * Show the form for editing the specified Permohonan record.
     */
    public function edit(Permohonan $permohonan): RedirectResponse
    {
        Gate::authorize('update', $permohonan);

        return redirect()->route('permohonan.index');
    }

    /**
     * Update the specified Permohonan record in storage.
     */
    public function update(UpdatePermohonanRequest $request, Permohonan $permohonan): RedirectResponse
    {
        Gate::authorize('update', $permohonan);

        $data = $request->validated();

        DB::transaction(function () use ($permohonan, $data): void {
            $permohonan->update($data);
        });

        return redirect()->route('permohonan.index')->with('success', 'Data permohonan keimigrasian berhasil diperbarui.');
    }

    /**
     * Remove the specified Permohonan record from storage.
     */
    public function destroy(Request $request, Permohonan $permohonan): RedirectResponse
    {
        Gate::authorize('delete', $permohonan);

        DB::transaction(function () use ($permohonan): void {
            $permohonan->delete();
        });

        return redirect()->route('permohonan.index')->with('success', 'Data permohonan keimigrasian berhasil dihapus.');
    }

    /**
     * Display printable list of Permohonan records for browser PDF print.
     */
    public function printList(Request $request): Response
    {
        Gate::authorize('printList', Permohonan::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'layanan' => ['nullable', 'string', 'max:150'],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:120'],
        ]);

        $query = Permohonan::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['layanan'])) {
            $query->where('layanan', $validated['layanan']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        if (! empty($validated['kabupaten'])) {
            $query->where('kabupaten', $validated['kabupaten']);
        }

        $permohonanList = $query->orderBy('tanggal', 'desc')->orderBy('nama', 'asc')->get();

        return Inertia::render('permohonan/PrintList', [
            'permohonanList' => $permohonanList,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'layanan' => $validated['layanan'] ?? '',
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? '',
                'kabupaten' => $validated['kabupaten'] ?? '',
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
