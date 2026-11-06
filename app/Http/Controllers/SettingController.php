<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\DocumentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettingController extends Controller
{
    /**
     * Display application settings form.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Setting::class);

        return Inertia::render('settings/App', [
            'settings' => [
                'app_name' => Setting::getAppName(),
                'instansi_name' => Setting::getInstansiName(),
                'app_description' => Setting::getAppDescription(),
                'show_login_logo' => Setting::getShowLoginLogo(),
                'app_icon' => Setting::getPrimaryLogoPath(),
                'app_icon_url' => Setting::getPrimaryLogoUrl(),
                'primary_logo' => Setting::getPrimaryLogoSlot(),
                'logo_1' => Setting::getLogoPath(1),
                'logo_1_url' => Setting::getLogoUrl(1),
                'logo_2' => Setting::getLogoPath(2),
                'logo_2_url' => Setting::getLogoUrl(2),
                'logo_3' => Setting::getLogoPath(3),
                'logo_3_url' => Setting::getLogoUrl(3),
                'available_logos' => Setting::getAvailableLogos(),
            ],
            'can' => [
                'update' => $request->user()?->can('update', Setting::class) ?? false,
            ],
        ]);
    }

    /**
     * Update application settings.
     */
    public function update(UpdateSettingRequest $request, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('update', Setting::class);

        $validated = $request->validated();

        Setting::set('app_name', trim($validated['app_name']));

        if (array_key_exists('instansi_name', $validated)) {
            Setting::set('instansi_name', $validated['instansi_name'] ? trim($validated['instansi_name']) : null);
        }

        if (array_key_exists('app_description', $validated)) {
            Setting::set('app_description', $validated['app_description'] ? trim($validated['app_description']) : null);
        }

        if (array_key_exists('show_login_logo', $validated)) {
            Setting::set('show_login_logo', $request->boolean('show_login_logo') ? '1' : '0');
        }

        // Process each logo slot: 1, 2, 3
        for ($slot = 1; $slot <= 3; $slot++) {
            $oldPath = Setting::getLogoPath($slot);
            $removeKey = "remove_logo_{$slot}";
            $fileKey = "app_logo_{$slot}";

            $shouldRemove = $request->boolean($removeKey) || ($slot === 1 && $request->boolean('remove_icon'));

            if ($shouldRemove) {
                if ($oldPath !== null) {
                    try {
                        $storageService->deleteFile($oldPath);
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
                Setting::set('app_logo_'.$slot, null);
                if ($slot === 1) {
                    Setting::set('app_icon', null);
                }
            } else {
                $file = $request->file($fileKey) ?? ($slot === 1 ? $request->file('app_icon') : null);
                if ($file instanceof UploadedFile) {
                    $newPath = $storageService->storePublicPhoto($file, 'settings/icons');
                    Setting::set('app_logo_'.$slot, $newPath);
                    if ($slot === 1) {
                        Setting::set('app_icon', $newPath);
                    }

                    if ($oldPath !== null && $oldPath !== $newPath) {
                        try {
                            $storageService->deleteFile($oldPath);
                        } catch (\Throwable $e) {
                            report($e);
                        }
                    }
                }
            }
        }

        // Update primary logo setting if provided and valid (has active file)
        if (array_key_exists('primary_logo', $validated) && in_array((int) $validated['primary_logo'], [1, 2, 3], true)) {
            $candidateSlot = (int) $validated['primary_logo'];
            if (Setting::getLogoPath($candidateSlot) !== null) {
                Setting::set('primary_logo', (string) $candidateSlot);
            }
        }

        // If the current primary slot has no file, fallback to the first available slot
        $activePrimary = Setting::getPrimaryLogoSlot();
        if (Setting::getLogoPath($activePrimary) === null) {
            for ($s = 1; $s <= 3; $s++) {
                if (Setting::getLogoPath($s) !== null) {
                    Setting::set('primary_logo', (string) $s);
                    break;
                }
            }
        }

        // Sync legacy app_icon key with primary logo path
        Setting::set('app_icon', Setting::getPrimaryLogoPath());

        return redirect()->route('pengaturan.index')->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }

    /**
     * Stream public application icon / logo.
     */
    public function icon(?string $slot = null): BinaryFileResponse
    {
        if ($slot !== null && in_array((int) $slot, [1, 2, 3], true)) {
            $iconPath = Setting::getLogoPath((int) $slot);
        } else {
            $iconPath = Setting::getPrimaryLogoPath();
        }

        if ($iconPath === null || trim($iconPath) === '') {
            abort(404, 'Ikon aplikasi belum diatur.');
        }

        if (str_contains($iconPath, '..') || str_contains($iconPath, "\0") || ! str_starts_with($iconPath, 'settings/icons/')) {
            abort(404, 'Path berkas ikon tidak valid.');
        }

        $disk = null;
        if (Storage::disk('public')->exists($iconPath)) {
            $disk = 'public';
        } elseif (Storage::disk('local')->exists($iconPath)) {
            $disk = 'local';
        }

        if ($disk === null) {
            abort(404, 'Berkas ikon aplikasi tidak ditemukan.');
        }

        $mimeType = Storage::disk($disk)->mimeType($iconPath);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        if (! is_string($mimeType) || ! in_array($mimeType, $allowedMimes, true)) {
            abort(415, 'Format berkas ikon tidak didukung.');
        }

        return response()->file(Storage::disk($disk)->path($iconPath), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
