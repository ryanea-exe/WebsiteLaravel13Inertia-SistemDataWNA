<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenerbitanRequest;
use App\Http\Requests\UpdatePenerbitanRequest;
use App\Models\Penerbitan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PenerbitanController extends Controller
{
    /**
     * Display a listing of Penerbitan records with search, filter, sort, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Penerbitan::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'layanan' => ['nullable', 'string', 'max:150'],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:120'],
            'sort_by' => ['nullable', 'string', Rule::in(['tanggal', 'nama', 'no_paspor', 'layanan', 'kewarganegaraan', 'kabupaten', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $query = Penerbitan::query();

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

        $sortBy = $validated['sort_by'] ?? null;
        $sortOrder = $validated['sort_order'] ?? null;
        $perPage = (int) ($validated['per_page'] ?? 50);

        $query->orderBy($sortBy ?? 'tanggal', $sortOrder ?? 'desc');

        $penerbitan = $query->paginate($perPage)->withQueryString();

        $layananOptions = Penerbitan::query()
            ->whereNotNull('layanan')
            ->where('layanan', '!=', '')
            ->distinct()
            ->orderBy('layanan')
            ->pluck('layanan');

        $kewarganegaraanOptions = Penerbitan::query()
            ->whereNotNull('kewarganegaraan')
            ->where('kewarganegaraan', '!=', '')
            ->distinct()
            ->orderBy('kewarganegaraan')
            ->pluck('kewarganegaraan');

        $kabupatenOptions = Penerbitan::query()
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->distinct()
            ->orderBy('kabupaten')
            ->pluck('kabupaten');

        $user = $request->user();

        return Inertia::render('penerbitan/Index', [
            'penerbitan' => $penerbitan,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'layanan' => $validated['layanan'] ?? '',
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? '',
                'kabupaten' => $validated['kabupaten'] ?? '',
                'sort_by' => $validated['sort_by'] ?? '',
                'sort_order' => $validated['sort_order'] ?? '',
                'per_page' => $perPage,
            ],
            'layananOptions' => $layananOptions,
            'kewarganegaraanOptions' => $kewarganegaraanOptions,
            'kabupatenOptions' => $kabupatenOptions,
            'can' => [
                'create' => $user?->can('create', Penerbitan::class) ?? false,
                'printList' => $user?->can('printList', Penerbitan::class) ?? false,
            ],
        ]);
    }

    /**
     * Show the form for creating a new Penerbitan record.
     */
    public function create(): RedirectResponse
    {
        Gate::authorize('create', Penerbitan::class);

        return redirect()->route('penerbitan.index');
    }

    /**
     * Store a newly created Penerbitan record in storage.
     */
    public function store(StorePenerbitanRequest $request): RedirectResponse
    {
        Gate::authorize('create', Penerbitan::class);

        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            Penerbitan::create($data);
        });

        return redirect()->route('penerbitan.index')->with('success', 'Data penerbitan berhasil ditambahkan.');
    }

    /**
     * Display the specified Penerbitan record.
     */
    public function show(Request $request, Penerbitan $penerbitan): Response|JsonResponse
    {
        Gate::authorize('view', $penerbitan);

        if ($request->wantsJson()) {
            return response()->json(['penerbitan' => $penerbitan]);
        }

        return Inertia::render('penerbitan/Show', [
            'penerbitan' => $penerbitan,
        ]);
    }

    /**
     * Show the form for editing the specified Penerbitan record.
     */
    public function edit(Penerbitan $penerbitan): RedirectResponse
    {
        Gate::authorize('update', $penerbitan);

        return redirect()->route('penerbitan.index');
    }

    /**
     * Update the specified Penerbitan record in storage.
     */
    public function update(UpdatePenerbitanRequest $request, Penerbitan $penerbitan): RedirectResponse
    {
        Gate::authorize('update', $penerbitan);

        $data = $request->validated();

        DB::transaction(function () use ($penerbitan, $data): void {
            $penerbitan->update($data);
        });

        return redirect()->route('penerbitan.index')->with('success', 'Data penerbitan berhasil diperbarui.');
    }

    /**
     * Remove the specified Penerbitan record from storage.
     */
    public function destroy(Request $request, Penerbitan $penerbitan): RedirectResponse
    {
        Gate::authorize('delete', $penerbitan);

        DB::transaction(function () use ($penerbitan): void {
            $penerbitan->delete();
        });

        return redirect()->route('penerbitan.index')->with('success', 'Data penerbitan berhasil dihapus.');
    }

    /**
     * Display printable list of Penerbitan records for browser PDF print.
     */
    public function printList(Request $request): Response
    {
        Gate::authorize('printList', Penerbitan::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'layanan' => ['nullable', 'string', 'max:150'],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:120'],
        ]);

        $query = Penerbitan::query();

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

        $penerbitanList = $query->orderBy('tanggal', 'desc')->orderBy('nama', 'asc')->get();

        return Inertia::render('penerbitan/PrintList', [
            'penerbitanList' => $penerbitanList,
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
