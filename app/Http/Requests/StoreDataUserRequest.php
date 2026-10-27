<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\DocumentStorageService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreDataUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,username', Rule::notIn(['superadmin', 'Superadmin', 'SUPERADMIN'])],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::enum(UserRole::class)],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
            'foto' => DocumentStorageService::photoRules(false),
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pengguna wajib diisi.',
            'nama.max' => 'Nama pengguna maksimal 150 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 80 karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, tanda hubung, titik, dan garis bawah.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'username.not_in' => 'Username tersebut tidak diizinkan.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'role.required' => 'Role akun wajib dipilih.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'foto.file' => 'Berkas foto harus berupa file yang valid.',
            'foto.mimes' => 'Format foto harus berupa JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2048 KB (2 MB).',
        ];
    }
}
