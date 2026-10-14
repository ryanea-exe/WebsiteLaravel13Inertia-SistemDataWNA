<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreDataUserRequest;
use App\Http\Requests\UpdateDataUserRequest;
use App\Models\User;
use App\Services\DocumentStorageService;
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

class DataUserController extends Controller
{
    /**
     * Display a listing of user accounts with search, role filtering, sorting, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', Rule::in(['admin', 'user'])],
            'sort_by' => ['nullable', 'string', Rule::in(['nama', 'username', 'role', 'terakhir_login', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $query = User::query();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['role'])) {
            $query->role($validated['role']);
        }

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($perPage)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'nama' => $user->nama,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->value,
                'role_label' => $user->role === UserRole::Admin ? 'Admin' : 'User',
                'foto' => $user->foto,
                'foto_url' => $user->foto ? route('data-user.photo', $user->id) : null,
                'terakhir_login' => $user->terakhir_login?->toISOString(),
                'created_at' => $user->created_at?->toISOString(),
            ]);

        $authUser = $request->user();

        return Inertia::render('data-user/Index', [
            'users' => $users,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'role' => $validated['role'] ?? '',
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'roleOptions' => [
                ['value' => 'admin', 'label' => 'Admin'],
                ['value' => 'user', 'label' => 'User'],
            ],
            'can' => [
                'create' => $authUser?->can('create', User::class) ?? false,
            ],
            'authUserId' => $authUser?->id,
        ]);
    }

    /**
     * Store a newly created user account.
     */
    public function store(StoreDataUserRequest $request, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $data = $request->validated();
        $photoPath = null;

        if ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            if ($photo instanceof UploadedFile) {
                $photoPath = $storageService->storePrivatePhoto($photo, 'users/photos');
                $data['foto'] = $photoPath;
            }
        }

        $data['name'] = $data['nama'];

        try {
            DB::transaction(function () use ($data): void {
                User::create($data);
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

        return redirect()->route('data-user.index')->with('success', 'Data pengguna berhasil ditambahkan.');
    }

    /**
     * Update the specified user account.
     */
    public function update(UpdateDataUserRequest $request, User $user, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('update', $user);

        $data = $request->validated();
        $authUser = $request->user();

        // Anti-lockout Rule 1: Admin cannot demote their own account
        if ($authUser && $authUser->id === $user->id && $data['role'] !== UserRole::Admin->value) {
            abort(403, 'Anda tidak dapat menurunkan role akun Anda sendiri.');
        }

        // Handle password update: only update if new password is provided
        if (empty($data['password'])) {
            unset($data['password']);
        }
        unset($data['password_confirmation']);

        // Handle avatar photo update
        $newPhotoPath = null;
        $oldPhotoPath = $user->foto;

        if ($request->boolean('remove_foto')) {
            $data['foto'] = null;
        } elseif ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            if ($photo instanceof UploadedFile) {
                $newPhotoPath = $storageService->storePrivatePhoto($photo, 'users/photos');
                $data['foto'] = $newPhotoPath;
            }
        } else {
            unset($data['foto']);
        }
        unset($data['remove_foto']);

        $data['name'] = $data['nama'];

        try {
            DB::transaction(function () use ($user, $data): void {
                /** @var User $target */
                $target = User::query()->where('id', $user->id)->lockForUpdate()->firstOrFail();

                // Anti-lockout Rule 2: Cannot demote the last active Admin
                if ($target->isAdmin() && $data['role'] !== UserRole::Admin->value) {
                    $adminCount = User::query()->where('role', UserRole::Admin->value)->lockForUpdate()->count();
                    if ($adminCount <= 1) {
                        abort(422, 'Tidak dapat mengubah role admin terakhir yang aktif.');
                    }
                }

                $target->update($data);
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

        return redirect()->route('data-user.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user account.
     */
    public function destroy(Request $request, User $user, DocumentStorageService $storageService): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $authUser = $request->user();

        // Anti-lockout Rule 1: Admin cannot delete their own account
        if ($authUser && $authUser->id === $user->id) {
            abort(403, 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $oldPhotoPath = $user->foto;

        DB::transaction(function () use ($user): void {
            /** @var User $target */
            $target = User::query()->where('id', $user->id)->lockForUpdate()->firstOrFail();

            // Anti-lockout Rule 2: Cannot delete the last active Admin
            if ($target->isAdmin()) {
                $adminCount = User::query()->where('role', UserRole::Admin->value)->lockForUpdate()->count();
                if ($adminCount <= 1) {
                    abort(422, 'Tidak dapat menghapus admin terakhir yang aktif.');
                }
            }

            $target->delete();
        });

        // Post-commit cleanup: safely delete photo file only if not referenced by another user
        if ($oldPhotoPath !== null) {
            $isStillInUse = User::query()->where('id', '!=', $user->id)->where('foto', $oldPhotoPath)->exists();
            if (! $isStillInUse) {
                try {
                    $storageService->deleteFile($oldPhotoPath);
                } catch (\Throwable $storageError) {
                    report($storageError);
                }
            }
        }

        return redirect()->route('data-user.index')->with('success', 'Data pengguna berhasil dihapus.');
    }

    /**
     * Stream protected user avatar photo with authorization and authentication.
     */
    public function photo(User $user): BinaryFileResponse
    {
        Gate::authorize('view', $user);

        if (! $user->foto) {
            abort(404, 'Berkas foto tidak ditemukan.');
        }

        $photoPath = (string) $user->foto;

        // Path traversal and directory jail check
        if (str_contains($photoPath, '..') || str_contains($photoPath, "\0") || (! str_starts_with($photoPath, 'users/photos/') && ! str_starts_with($photoPath, 'photos/'))) {
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
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
