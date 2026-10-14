<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Http\Requests\StoreAffidavitRequest;
use App\Http\Requests\UpdateAffidavitRequest;
use App\Models\Affidavit;
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

class AffidavitController extends Controller
{
    /**
     * Display a listing of Affidavit records with search, filter, sort, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Affidavit::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', 'string', Rule::enum(Gender::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', Rule::in(['nama', 'no_register', 'no_paspor', 'kewarganegaraan', 'tanggal_lahir', 'masa_berlaku', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $query = Affidavit::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $validated['jenis_kelamin']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query->orderBy($sortBy, $sortOrder);

        $affidavit = $query->paginate($perPage)->withQueryString();

        $kewarganegaraanOptions = Affidavit::query()
            ->whereNotNull('kewarganegaraan')
            ->distinct()
            ->orderBy('kewarganegaraan')
            ->pluck('kewarganegaraan');

        $user = $request->user();

        return Inertia::render('affidavit/Index', [
            'affidavit' => $affidavit,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? '',
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? '',
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'kewarganegaraanOptions' => $kewarganegaraanOptions,
            'genderOptions' => array_map(
                fn (Gender $case) => ['value' => $case->value, 'label' => $case->label()],
                Gender::cases()
            ),
            'can' => [
                'create' => $user?->can('create', Affidavit::class) ?? false,
                'printList' => $user?->can('printList', Affidavit::class) ?? false,
                'export' => $user?->can('export', Affidavit::class) ?? false,
            ],
        ]);
    }

    /**
     * Show the form for creating a new Affidavit record.
     */
    public function create(): RedirectResponse
    {
        Gate::authorize('create', Affidavit::class);

        return redirect()->route('affidavit.index');
    }

    /**
     * Store a newly created Affidavit record in storage.
     */
    public function store(StoreAffidavitRequest $request, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('create', Affidavit::class);

        $data = $request->validated();
        $photoPath = null;

        if ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            if ($photo instanceof UploadedFile) {
                $photoPath = $storageService->storePrivatePhoto($photo, 'affidavit/photos');
                $data['foto'] = $photoPath;
            }
        }

        try {
            DB::transaction(function () use ($data): void {
                Affidavit::create($data);
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

        return redirect()->route('affidavit.index')->with('success', 'Data Pendaftaran Affidavit berhasil ditambahkan.');
    }

    /**
     * Display the specified Affidavit record.
     */
    public function show(Request $request, Affidavit $affidavit): Response|JsonResponse
    {
        Gate::authorize('view', $affidavit);

        if ($request->wantsJson()) {
            return response()->json(['affidavit' => $affidavit]);
        }

        return Inertia::render('affidavit/Show', [
            'affidavit' => $affidavit,
        ]);
    }

    /**
     * Show the form for editing the specified Affidavit record.
     */
    public function edit(Affidavit $affidavit): RedirectResponse
    {
        Gate::authorize('update', $affidavit);

        return redirect()->route('affidavit.index');
    }

    /**
     * Update the specified Affidavit record in storage.
     */
    public function update(UpdateAffidavitRequest $request, Affidavit $affidavit, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('update', $affidavit);

        $data = $request->validated();
        $newPhotoPath = null;
        $oldPhotoPath = $affidavit->foto;

        if ($request->boolean('remove_foto')) {
            $data['foto'] = null;
        } elseif ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            if ($photo instanceof UploadedFile) {
                $newPhotoPath = $storageService->storePrivatePhoto($photo, 'affidavit/photos');
                $data['foto'] = $newPhotoPath;
            }
        } else {
            unset($data['foto']);
        }
        unset($data['remove_foto']);

        try {
            DB::transaction(function () use ($affidavit, $data): void {
                $affidavit->update($data);
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

        // Post-commit old photo cleanup: safely executed so storage errors do not revert DB changes or throw 500
        if ($oldPhotoPath !== null && ($newPhotoPath !== null || $request->boolean('remove_foto'))) {
            try {
                $storageService->deleteFile($oldPhotoPath);
            } catch (\Throwable $storageError) {
                report($storageError);
            }
        }

        return redirect()->route('affidavit.index')->with('success', 'Data Pendaftaran Affidavit berhasil diperbarui.');
    }

    /**
     * Remove the specified Affidavit record from storage.
     */
    public function destroy(Request $request, Affidavit $affidavit, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('delete', $affidavit);

        $photoPath = $affidavit->foto;

        DB::transaction(function () use ($affidavit): void {
            $affidavit->delete();
        });

        if ($photoPath !== null) {
            try {
                $storageService->deleteFile($photoPath);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('affidavit.index')->with('success', 'Data Pendaftaran Affidavit berhasil dihapus.');
    }

    /**
     * Stream protected Affidavit photo with authorization and authentication.
     */
    public function photo(Affidavit $affidavit): BinaryFileResponse
    {
        Gate::authorize('view', $affidavit);

        if (! $affidavit->foto) {
            abort(404, 'Berkas foto tidak ditemukan.');
        }

        $photoPath = (string) $affidavit->foto;

        // Path traversal and directory jail check
        if (str_contains($photoPath, '..') || str_contains($photoPath, "\0") || ! str_starts_with($photoPath, 'affidavit/photos/')) {
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
     * Export filtered Affidavit records to CSV with formula injection sanitization.
     */
    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('export', Affidavit::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', 'string', Rule::enum(Gender::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', Rule::in(['nama', 'no_register', 'no_paspor', 'kewarganegaraan', 'tanggal_lahir', 'masa_berlaku', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $query = Affidavit::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $validated['jenis_kelamin']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        $sortBy = $validated['sort_by'] ?? 'nama';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $filename = 'data-affidavit-'.now()->format('Ymd-His').'.csv';

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
                'Tempat Lahir',
                'Tanggal Lahir',
                'Jenis Kelamin',
                'Kewarganegaraan',
                'Masa Berlaku',
                'Nama Ayah',
                'Kewarganegaraan Ayah',
                'Nama Ibu',
                'Kewarganegaraan Ibu',
                'Nomor Telepon',
                'Alamat',
            ]);

            $index = 1;
            $query->chunk(250, function ($records) use ($handle, &$index): void {
                foreach ($records as $record) {
                    $row = [
                        $index++,
                        $record->no_register,
                        $record->no_paspor,
                        $record->nama,
                        $record->tempat_lahir ?? '-',
                        $record->tanggal_lahir?->format('Y-m-d') ?? '-',
                        $record->jenis_kelamin->label(),
                        $record->kewarganegaraan,
                        $record->masa_berlaku?->format('Y-m-d') ?? '-',
                        $record->nama_ayah ?? '-',
                        $record->kewarganegaraan_ayah ?? '-',
                        $record->nama_ibu ?? '-',
                        $record->kewarganegaraan_ibu ?? '-',
                        $record->nomor_telepon ?? '-',
                        $record->alamat ?? '-',
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
     * Display printable list of Affidavit records for browser PDF print.
     */
    public function printList(Request $request): Response
    {
        Gate::authorize('printList', Affidavit::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', 'string', Rule::enum(Gender::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Affidavit::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $validated['jenis_kelamin']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        $affidavitList = $query->orderBy('nama', 'asc')->get();

        return Inertia::render('affidavit/PrintList', [
            'affidavitList' => $affidavitList,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? '',
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? '',
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
