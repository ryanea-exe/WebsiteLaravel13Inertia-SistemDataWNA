<?php

namespace App\Http\Controllers\Settings;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use App\Services\DocumentStorageService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->value,
                'role_label' => $user->role === UserRole::Admin ? 'Administrator' : 'Petugas',
                'foto' => $user->foto,
                'foto_url' => $user->foto_url,
                'terakhir_login' => $user->terakhir_login?->toISOString(),
                'created_at' => $user->created_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, DocumentStorageService $storageService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        // 1. Update Name/Nama
        $nama = $validated['nama'] ?? $validated['name'] ?? $user->nama;
        $user->nama = $nama;
        $user->name = $nama;

        // 2. Update Username if provided
        if (! empty($validated['username'])) {
            $user->username = $validated['username'];
        }

        // 3. Update Email if provided
        if (array_key_exists('email', $validated)) {
            if ($user->email !== $validated['email']) {
                $user->email = $validated['email'];
                $user->email_verified_at = null;
            }
        }

        // 4. Update Password if provided
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        // 5. Handle Avatar Photo
        $newPhotoPath = null;
        $oldPhotoPath = $user->foto;

        if ($request->boolean('remove_foto')) {
            $user->foto = null;
        } elseif ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            if ($photo instanceof UploadedFile) {
                $newPhotoPath = $storageService->storePrivatePhoto($photo, 'users/photos');
                $user->foto = $newPhotoPath;
            }
        }

        try {
            DB::transaction(function () use ($user): void {
                $user->save();
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

        // Post-commit cleanup: safely delete old photo file only if not referenced by another user
        if ($oldPhotoPath !== null && ($newPhotoPath !== null || $request->boolean('remove_foto'))) {
            $isStillInUse = User::query()->where('id', '!=', $user->id)->where('foto', $oldPhotoPath)->exists();
            if (! $isStillInUse) {
                try {
                    $storageService->deleteFile($oldPhotoPath);
                } catch (\Throwable $storageError) {
                    report($storageError);
                }
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profil berhasil diperbarui.']);

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
