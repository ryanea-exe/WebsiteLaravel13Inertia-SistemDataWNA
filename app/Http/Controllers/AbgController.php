<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Http\Requests\StoreAbgRequest;
use App\Http\Requests\UpdateAbgRequest;
use App\Models\Abg;
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

class AbgController extends Controller
{
    /**
     * Display a listing of ABG records with search, filter, sort, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Abg::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', 'string', Rule::enum(Gender::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', Rule::in(['nama', 'no_register', 'no_paspor', 'kewarganegaraan', 'tanggal_lahir', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $query = Abg::query();

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

        $abg = $query->paginate($perPage)->withQueryString();

        $kewarganegaraanOptions = Abg::query()
            ->whereNotNull('kewarganegaraan')
            ->distinct()
            ->orderBy('kewarganegaraan')
            ->pluck('kewarganegaraan');

        $user = $request->user();

        return Inertia::render('abg/Index', [
            'abg' => $abg,
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
                'create' => $user?->can('create', Abg::class) ?? false,
                'printList' => $user?->can('printList', Abg::class) ?? false,
                'export' => $user?->can('export', Abg::class) ?? false,
            ],
        ]);
    }

    /**
     * Show the form for creating a new ABG record.
     */
    public function create(): RedirectResponse
    {
        Gate::authorize('create', Abg::class);

        return redirect()->route('abg.index');
    }

    /**
     * Store a newly created ABG record in storage.
     */
    public function store(StoreAbgRequest $request, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('create', Abg::class);

        $data = $request->validated();
        $photoPath = null;

        if ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            if ($photo instanceof UploadedFile) {
                $photoPath = $storageService->storePrivatePhoto($photo, 'abg/photos');
                $data['foto'] = $photoPath;
            }
        }

        try {
            DB::transaction(function () use ($data): void {
                Abg::create($data);
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

        return redirect()->route('abg.index')->with('success', 'Data Pendaftaran ABG berhasil ditambahkan.');
    }

    /**
     * Display the specified ABG record.
     */
    public function show(Request $request, Abg $abg): Response|JsonResponse
    {
        Gate::authorize('view', $abg);

        if ($request->wantsJson()) {
            return response()->json(['abg' => $abg]);
        }

        return Inertia::render('abg/Show', [
            'abg' => $abg,
        ]);
    }

    /**
     * Show the form for editing the specified ABG record.
     */
    public function edit(Abg $abg): RedirectResponse
    {
        Gate::authorize('update', $abg);

        return redirect()->route('abg.index');
    }

    /**
     * Update the specified ABG record in storage.
     */
    public function update(UpdateAbgRequest $request, Abg $abg, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('update', $abg);

        $data = $request->validated();
        $newPhotoPath = null;
        $oldPhotoPath = $abg->foto;

        if ($request->boolean('remove_foto')) {
            $data['foto'] = null;
        } elseif ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            if ($photo instanceof UploadedFile) {
                $newPhotoPath = $storageService->storePrivatePhoto($photo, 'abg/photos');
                $data['foto'] = $newPhotoPath;
            }
        } else {
            unset($data['foto']);
        }
        unset($data['remove_foto']);

        try {
            DB::transaction(function () use ($abg, $data): void {
                $abg->update($data);
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

        return redirect()->route('abg.index')->with('success', 'Data Pendaftaran ABG berhasil diperbarui.');
    }

    /**
     * Remove the specified ABG record from storage.
     */
    public function destroy(Request $request, Abg $abg, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('delete', $abg);

        $photoPath = $abg->foto;

        DB::transaction(function () use ($abg): void {
            $abg->delete();
        });

        if ($photoPath !== null) {
            try {
                $storageService->deleteFile($photoPath);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('abg.index')->with('success', 'Data Pendaftaran ABG berhasil dihapus.');
    }

    /**
     * Stream protected ABG photo with authorization and authentication.
     */
    public function photo(Abg $abg): BinaryFileResponse
    {
        Gate::authorize('view', $abg);

        if (! $abg->foto) {
            abort(404, 'Berkas foto tidak ditemukan.');
        }

        $photoPath = (string) $abg->foto;

        // Path traversal and directory jail check
        if (str_contains($photoPath, '..') || str_contains($photoPath, "\0") || ! str_starts_with($photoPath, 'abg/photos/')) {
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

        return response()->file(Storage::disk($disk)->path($photoPath), [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, no-cache, must-revalidate',
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Export filtered ABG records to CSV with formula injection sanitization.
     */
    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('export', Abg::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', 'string', Rule::enum(Gender::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', Rule::in(['nama', 'no_register', 'no_paspor', 'kewarganegaraan', 'tanggal_lahir', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $query = Abg::query();

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

        $filename = 'data-abg-'.now()->format('Ymd-His').'.csv';

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
                        $record->nama_ayah ?? '-',
                        $record->kewarganegaraan_ayah ?? '-',
                        $record->nama_ibu ?? '-',
                        $record->kewarganegaraan_ibu ?? '-',
                        $record->nomor_telepon ?? '-',
                        $record->alamat ?? '-',
                    ];

                    $sanitizedRow = array_map(fn ($val) => $this->sanitizeCsvValue($val), $row);
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
     * Display printable list of ABG records for browser PDF print.
     */
    public function printList(Request $request): Response
    {
        Gate::authorize('printList', Abg::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', 'string', Rule::enum(Gender::class)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Abg::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $validated['jenis_kelamin']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        $abgList = $query->orderBy('nama', 'asc')->get();

        return Inertia::render('abg/PrintList', [
            'abgList' => $abgList,
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

    /**
     * Sanitize a value against CSV formula injection (OWASP).
     */
    private function sanitizeCsvValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $str = (string) $value;

        // If string starts with dangerous formula trigger characters
        if (preg_match('/^[=\+\-@\t\r]/', $str) === 1) {
            // Keep plain numbers intact
            if (is_numeric($str)) {
                return $str;
            }

            return "'".$str;
        }

        return $str;
    }
}
