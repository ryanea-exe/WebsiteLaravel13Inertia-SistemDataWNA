<?php

namespace App\Http\Controllers;

use App\Models\Abg;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EarlyWarningAbgController extends Controller
{
    /**
     * Display a listing of ABG within the early warning period with categorization, filters, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewEarlyWarning', Abg::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', Rule::in(['semua', 'kurang_18', 'kurang_21', 'lewat_21'])],
            'jenis_kelamin' => ['nullable', 'string', Rule::in(['L', 'P'])],
            'kabupaten' => ['nullable', 'string', Rule::in(Abg::KABUPATEN_OPTIONS)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', Rule::in(['tanggal_lahir', 'nama', 'no_register', 'kewarganegaraan', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $referenceDate = Carbon::now('Asia/Jakarta')->startOfDay();
        $kategori = $validated['kategori'] ?? 'semua';
        $query = Abg::query()->kategoriPeringatan($kategori, $referenceDate);

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $validated['jenis_kelamin']);
        }

        if (! empty($validated['kabupaten'])) {
            $query->where('kabupaten', $validated['kabupaten']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        $sortBy = $validated['sort_by'] ?? 'tanggal_lahir';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $perPage = (int) ($validated['per_page'] ?? 50);

        $query->orderBy($sortBy, $sortOrder);

        $paginated = $query->paginate($perPage)->withQueryString();

        $paginated->getCollection()->transform(function (Abg $item) use ($referenceDate) {
            $dob = $item->tanggal_lahir ? Carbon::parse($item->tanggal_lahir)->timezone('Asia/Jakarta')->startOfDay() : null;
            $urgensi = $item->getEarlyWarningStatus($referenceDate) ?? 'lewat_21';

            $target = match ($urgensi) {
                'kurang_18' => $dob?->copy()->addYears(18),
                default => $dob?->copy()->addYears(21),
            };

            $sisaHari = $target ? (int) $referenceDate->diffInDays($target, false) : 0;

            $statusLabel = match ($urgensi) {
                'kurang_18' => 'Menjelang 18 Tahun',
                'kurang_21' => 'Menjelang 21 Tahun',
                'lewat_21' => 'Lewat 21 Tahun (Kedaluwarsa)',
                default => '-',
            };

            $sisaWaktuLabel = match ($urgensi) {
                'kurang_18' => "Sisa {$sisaHari} hari (Menuju 18 Th)",
                'kurang_21' => "Sisa {$sisaHari} hari (Menuju 21 Th)",
                'lewat_21' => 'Lewat '.abs($sisaHari).' hari (Kedaluwarsa)',
                default => '-',
            };

            $umur = $item->umur;
            if (empty($umur) || $umur === '-') {
                if ($dob) {
                    $years = (int) $dob->diffInYears($referenceDate);
                    $days = (int) $dob->copy()->addYears($years)->diffInDays($referenceDate);
                    $umur = "{$years} tahun {$days} hari";
                } else {
                    $umur = '-';
                }
            }

            $item->setAttribute('urgensi', $urgensi);
            $item->setAttribute('target_milestone', $target?->format('Y-m-d'));
            $item->setAttribute('sisa_hari', $sisaHari);
            $item->setAttribute('status_label', $statusLabel);
            $item->setAttribute('sisa_waktu_label', $sisaWaktuLabel);
            $item->setAttribute('umur', $umur);

            $phone = Abg::normalizePhoneNumber($item->nomor_telepon);
            $message = $item->generateWhatsappMessage($referenceDate);
            $item->setAttribute('whatsapp_url', ($phone && $message) ? 'https://wa.me/'.$phone.'?text='.rawurlencode($message) : null);

            return $item;
        });

        $counts = Abg::getEarlyWarningCounts($referenceDate);

        $kewarganegaraanOptions = Abg::query()
            ->whereNotNull('kewarganegaraan')
            ->distinct()
            ->orderBy('kewarganegaraan')
            ->pluck('kewarganegaraan');

        $user = $request->user();

        return Inertia::render('early-warning-abg/Index', [
            'abg' => $paginated,
            'counts' => $counts,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'kategori' => $kategori,
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? '',
                'kabupaten' => $validated['kabupaten'] ?? '',
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? '',
                'sort_by' => $validated['sort_by'] ?? '',
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'kewarganegaraanOptions' => $kewarganegaraanOptions,
            'kabupatenOptions' => Abg::KABUPATEN_OPTIONS,
            'can' => [
                'printList' => $user?->can('viewEarlyWarning', Abg::class) ?? false,
            ],
        ]);
    }

    /**
     * Display printable list of early warning ABG records for browser PDF print.
     */
    public function printList(Request $request): Response
    {
        Gate::authorize('viewEarlyWarning', Abg::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', Rule::in(['semua', 'kurang_18', 'kurang_21', 'lewat_21'])],
            'jenis_kelamin' => ['nullable', 'string', Rule::in(['L', 'P'])],
            'kabupaten' => ['nullable', 'string', Rule::in(Abg::KABUPATEN_OPTIONS)],
            'kewarganegaraan' => ['nullable', 'string', 'max:100'],
        ]);

        $referenceDate = Carbon::now('Asia/Jakarta')->startOfDay();
        $kategori = $validated['kategori'] ?? 'semua';
        $query = Abg::query()->kategoriPeringatan($kategori, $referenceDate);

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $validated['jenis_kelamin']);
        }

        if (! empty($validated['kabupaten'])) {
            $query->where('kabupaten', $validated['kabupaten']);
        }

        if (! empty($validated['kewarganegaraan'])) {
            $query->where('kewarganegaraan', $validated['kewarganegaraan']);
        }

        $records = $query->orderBy('tanggal_lahir', 'asc')->get();

        $records->transform(function (Abg $item) use ($referenceDate) {
            $dob = $item->tanggal_lahir ? Carbon::parse($item->tanggal_lahir)->timezone('Asia/Jakarta')->startOfDay() : null;
            $urgensi = $item->getEarlyWarningStatus($referenceDate) ?? 'lewat_21';

            $target = match ($urgensi) {
                'kurang_18' => $dob?->copy()->addYears(18),
                default => $dob?->copy()->addYears(21),
            };

            $sisaHari = $target ? (int) $referenceDate->diffInDays($target, false) : 0;

            $statusLabel = match ($urgensi) {
                'kurang_18' => 'Menjelang 18 Th',
                'kurang_21' => 'Menjelang 21 Th',
                'lewat_21' => 'Lewat 21 Th (Kedaluwarsa)',
                default => '-',
            };

            $sisaWaktuLabel = match ($urgensi) {
                'kurang_18' => "Sisa {$sisaHari} hari",
                'kurang_21' => "Sisa {$sisaHari} hari",
                'lewat_21' => 'Lewat '.abs($sisaHari).' hari',
                default => '-',
            };

            $umur = $item->umur;
            if (empty($umur) || $umur === '-') {
                if ($dob) {
                    $years = (int) $dob->diffInYears($referenceDate);
                    $days = (int) $dob->copy()->addYears($years)->diffInDays($referenceDate);
                    $umur = "{$years} tahun {$days} hari";
                } else {
                    $umur = '-';
                }
            }

            $item->setAttribute('urgensi', $urgensi);
            $item->setAttribute('target_milestone', $target?->format('Y-m-d'));
            $item->setAttribute('sisa_hari', $sisaHari);
            $item->setAttribute('status_label', $statusLabel);
            $item->setAttribute('sisa_waktu_label', $sisaWaktuLabel);
            $item->setAttribute('umur', $umur);

            $phone = Abg::normalizePhoneNumber($item->nomor_telepon);
            $message = $item->generateWhatsappMessage($referenceDate);
            $item->setAttribute('whatsapp_url', ($phone && $message) ? 'https://wa.me/'.$phone.'?text='.rawurlencode($message) : null);

            return $item;
        });

        $counts = Abg::getEarlyWarningCounts($referenceDate);

        return Inertia::render('early-warning-abg/PrintList', [
            'abg' => $records,
            'counts' => $counts,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'kategori' => $kategori,
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? '',
                'kabupaten' => $validated['kabupaten'] ?? '',
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? '',
            ],
            'printDate' => $referenceDate->translatedFormat('d F Y'),
        ]);
    }
}
