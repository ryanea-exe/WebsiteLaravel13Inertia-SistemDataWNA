<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Enums\JenisIzinTinggal;
use App\Http\Requests\StoreWnaRequest;
use App\Http\Requests\UpdateWnaRequest;
use App\Models\Wna;
use App\Services\CsvSanitizer;
use App\Services\DocumentStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WnaController extends Controller
{
    /**
     * Display a listing of WNA records with search, filter, sort, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Wna::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_izin_tinggal' => ['nullable', 'string', Rule::enum(JenisIzinTinggal::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', Rule::in(Wna::KABUPATEN_OPTIONS)],
            'sort_by' => ['nullable', 'string', Rule::in(['nama', 'no_register', 'no_paspor', 'kewarganegaraan', 'jenis_izin_tinggal', 'kabupaten', 'masa_berlaku', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $query = Wna::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_izin_tinggal'])) {
            $query->where('jenis_izin_tinggal', $validated['jenis_izin_tinggal']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        if (! empty($validated['kabupaten'])) {
            $query->where('kabupaten', $validated['kabupaten']);
        }

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 50);

        $query->orderBy($sortBy, $sortOrder);

        $wna = $query->paginate($perPage)->withQueryString();

        $kewarganegaraanOptions = Wna::query()
            ->whereNotNull('kewarganegaraan')
            ->distinct()
            ->orderBy('kewarganegaraan')
            ->pluck('kewarganegaraan');

        $user = $request->user();

        return Inertia::render('wna/Index', [
            'wna' => $wna,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'jenis_izin_tinggal' => $validated['jenis_izin_tinggal'] ?? '',
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? '',
                'kabupaten' => $validated['kabupaten'] ?? '',
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'kewarganegaraanOptions' => $kewarganegaraanOptions,
            'kabupatenOptions' => Wna::KABUPATEN_OPTIONS,
            'jenisIzinTinggalOptions' => array_map(
                fn (JenisIzinTinggal $case) => ['value' => $case->value, 'label' => $case->label()],
                JenisIzinTinggal::cases()
            ),
            'genderOptions' => array_map(
                fn (Gender $case) => ['value' => $case->value, 'label' => $case->label()],
                Gender::cases()
            ),
            'can' => [
                'create' => $user?->can('create', Wna::class) ?? false,
                'printList' => $user?->can('printList', Wna::class) ?? false,
                'export' => $user?->can('export', Wna::class) ?? false,
            ],
        ]);
    }

    /**
     * Show the form for creating a new WNA record.
     */
    public function create(): RedirectResponse
    {
        Gate::authorize('create', Wna::class);

        return redirect()->route('wna.index');
    }

    /**
     * Store a newly created WNA record in storage.
     */
    public function store(StoreWnaRequest $request, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('create', Wna::class);

        $data = $request->validated();
        $photoPath = null;

        if ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            if ($photo instanceof UploadedFile) {
                $photoPath = $storageService->storePrivatePhoto($photo, 'wna/photos');
                $data['foto'] = $photoPath;
            }
        }

        try {
            DB::transaction(function () use ($data): void {
                Wna::create($data);
            });
        } catch (\Throwable $e) {
            if ($photoPath !== null) {
                try {
                    $storageService->deleteFile($photoPath);
                } catch (\Throwable $cleanupError) {
                    report($cleanupError);
                }
            }

            throw $e;
        }

        return redirect()->route('wna.index')->with('success', 'Data WNA berhasil ditambahkan.');
    }

    /**
     * Display the specified WNA record.
     */
    public function show(Request $request, Wna $wna): Response|JsonResponse
    {
        Gate::authorize('view', $wna);

        if ($request->wantsJson()) {
            return response()->json(['wna' => $wna]);
        }

        return Inertia::render('wna/Show', [
            'wna' => $wna,
        ]);
    }

    /**
     * Show the form for editing the specified WNA record.
     */
    public function edit(Wna $wna): RedirectResponse
    {
        Gate::authorize('update', $wna);

        return redirect()->route('wna.index');
    }

    /**
     * Update the specified WNA record in storage.
     */
    public function update(UpdateWnaRequest $request, Wna $wna, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('update', $wna);

        $data = $request->validated();
        $newPhotoPath = null;
        $oldPhotoPath = $wna->foto;

        if ($request->boolean('remove_foto')) {
            $data['foto'] = null;
        } elseif ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            if ($photo instanceof UploadedFile) {
                $newPhotoPath = $storageService->storePrivatePhoto($photo, 'wna/photos');
                $data['foto'] = $newPhotoPath;
            }
        } else {
            unset($data['foto']);
        }
        unset($data['remove_foto']);

        try {
            DB::transaction(function () use ($wna, $data): void {
                $wna->update($data);
            });
        } catch (\Throwable $e) {
            if ($newPhotoPath !== null) {
                try {
                    $storageService->deleteFile($newPhotoPath);
                } catch (\Throwable $cleanupError) {
                    report($cleanupError);
                }
            }

            throw $e;
        }

        // Post-commit old photo cleanup: isolated so storage errors do not revert DB changes or throw 500
        if ($oldPhotoPath !== null && ($newPhotoPath !== null || $request->boolean('remove_foto'))) {
            try {
                $storageService->deleteFile($oldPhotoPath);
            } catch (\Throwable $storageError) {
                report($storageError);
            }
        }

        return redirect()->route('wna.index')->with('success', 'Data WNA berhasil diperbarui.');
    }

    /**
     * Remove the specified WNA record from storage.
     */
    public function destroy(Request $request, Wna $wna, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('delete', $wna);

        $photoPath = $wna->foto;

        DB::transaction(function () use ($wna): void {
            $wna->delete();
        });

        if ($photoPath !== null) {
            try {
                $storageService->deleteFile($photoPath);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('wna.index')->with('success', 'Data WNA berhasil dihapus.');
    }

    /**
     * Stream protected WNA photo with authorization and authentication.
     */
    public function photo(Wna $wna): BinaryFileResponse
    {
        Gate::authorize('view', $wna);

        if (! $wna->foto) {
            abort(404, 'Berkas foto tidak ditemukan.');
        }

        $photoPath = (string) $wna->foto;

        // Path traversal and directory jail check
        if (str_contains($photoPath, '..') || str_contains($photoPath, "\0") || ! str_starts_with($photoPath, 'wna/photos/')) {
            abort(404, 'Berkas foto tidak valid.');
        }

        $disk = null;
        if (Storage::disk('local')->exists($photoPath)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($photoPath)) {
            $disk = 'public';
        }

        if ($disk === null) {
            abort(404, 'Berkas foto tidak ditemukan.');
        }

        $mimeType = Storage::disk($disk)->mimeType($photoPath);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        if (! is_string($mimeType) || ! in_array($mimeType, $allowedMimes, true)) {
            abort(415, 'Format berkas foto tidak didukung.');
        }

        $response = response()->file(Storage::disk($disk)->path($photoPath), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    /**
     * Display printable individual WNA card.
     */
    public function printCard(Request $request, Wna $wna): Response
    {
        Gate::authorize('printCard', $wna);

        return Inertia::render('wna/Card', [
            'wna' => $wna,
            'printedAt' => now()->translatedFormat('d F Y H:i'),
        ]);
    }

    /**
     * Export filtered WNA records to CSV with formula injection sanitization.
     */
    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('export', Wna::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_izin_tinggal' => ['nullable', 'string', Rule::enum(JenisIzinTinggal::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', Rule::in(Wna::KABUPATEN_OPTIONS)],
            'sort_by' => ['nullable', 'string', Rule::in(['nama', 'no_register', 'no_paspor', 'kewarganegaraan', 'jenis_izin_tinggal', 'kabupaten', 'masa_berlaku', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $query = Wna::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_izin_tinggal'])) {
            $query->where('jenis_izin_tinggal', $validated['jenis_izin_tinggal']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        if (! empty($validated['kabupaten'])) {
            $query->where('kabupaten', $validated['kabupaten']);
        }

        $sortBy = $validated['sort_by'] ?? 'nama';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $filename = 'data-wna-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM so spreadsheet viewers render characters correctly
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No.',
                'Nomor Register',
                'Nomor Paspor',
                'Nama Lengkap',
                'Jenis Kelamin',
                'Kewarganegaraan',
                'Kabupaten',
                'Jenis Izin Tinggal',
                'Izin Tinggal Ke',
                'Masa Berlaku',
                'Sisa Hari',
                'Status Urgensi',
                'Penjamin',
                'Nomor Telepon',
            ]);

            $index = 1;
            $query->chunk(250, function ($records) use ($handle, &$index): void {
                foreach ($records as $record) {
                    $row = [
                        $index++,
                        $record->no_register,
                        $record->no_paspor,
                        $record->nama,
                        $record->jenis_kelamin->label(),
                        $record->kewarganegaraan,
                        $record->kabupaten ?? 'Belum ditentukan',
                        $record->jenis_izin_tinggal->value,
                        $record->izin_tinggal_ke ?? '-',
                        $record->masa_berlaku->format('Y-m-d'),
                        $record->sisa_hari,
                        $record->urgensi_label,
                        $record->penjamin ?? '-',
                        $record->nomor_telepon ?? '-',
                    ];

                    $sanitizedRow = CsvSanitizer::sanitizeRow($row);
                    fputcsv($handle, $sanitizedRow);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Display printable list of WNA records for browser PDF print.
     */
    public function printList(Request $request): Response
    {
        Gate::authorize('printList', Wna::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_izin_tinggal' => ['nullable', 'string', Rule::enum(JenisIzinTinggal::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', Rule::in(Wna::KABUPATEN_OPTIONS)],
        ]);

        $query = Wna::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_izin_tinggal'])) {
            $query->where('jenis_izin_tinggal', $validated['jenis_izin_tinggal']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        if (! empty($validated['kabupaten'])) {
            $query->where('kabupaten', $validated['kabupaten']);
        }

        $wnaList = $query->orderBy('nama', 'asc')->get();

        return Inertia::render('wna/PrintList', [
            'wnaList' => $wnaList,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'jenis_izin_tinggal' => $validated['jenis_izin_tinggal'] ?? '',
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
