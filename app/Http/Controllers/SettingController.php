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
                'app_icon' => Setting::get('app_icon'),
                'app_icon_url' => Setting::getAppIconUrl(),
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

        $oldIcon = Setting::get('app_icon');

        if ($request->boolean('remove_icon')) {
            if ($oldIcon !== null) {
                try {
                    $storageService->deleteFile($oldIcon);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
            Setting::set('app_icon', null);
        } elseif ($request->hasFile('app_icon')) {
            $file = $request->file('app_icon');
            if ($file instanceof UploadedFile) {
                $newIconPath = $storageService->storePublicPhoto($file, 'settings/icons');
                Setting::set('app_icon', $newIconPath);

                if ($oldIcon !== null && $oldIcon !== $newIconPath) {
                    try {
                        $storageService->deleteFile($oldIcon);
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
        }

        return redirect()->route('pengaturan.index')->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }

    /**
     * Stream public application icon.
     */
    public function icon(): BinaryFileResponse
    {
        $iconPath = Setting::get('app_icon');

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
