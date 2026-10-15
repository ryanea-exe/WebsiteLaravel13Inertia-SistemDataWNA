<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use App\Services\DocumentStorageService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->user();
        $userId = $user?->id;

        return [
            'nama' => ['required_without:name', 'nullable', 'string', 'max:150'],
            'name' => ['required_without:nama', 'nullable', 'string', 'max:150'],
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:80',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'current_password' => [
                'nullable',
                'required_with:password',
                'current_password',
            ],
            'password' => [
                'nullable',
                'required_with:password_confirmation,current_password',
                'string',
                Password::min(8),
                'confirmed',
            ],
            'foto' => DocumentStorageService::photoRules(false),
            'remove_foto' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pengguna wajib diisi.',
            'nama.required_without' => 'Nama pengguna wajib diisi.',
            'nama.max' => 'Nama pengguna maksimal 150 karakter.',
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.required_without' => 'Nama pengguna wajib diisi.',
            'name.max' => 'Nama pengguna maksimal 150 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 80 karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, tanda hubung, titik, dan garis bawah.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'current_password.required_with' => 'Kata sandi saat ini wajib diisi untuk mengubah kata sandi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required_with' => 'Kata sandi baru wajib diisi jika kata sandi saat ini diisi.',
            'password.min' => 'Kata sandi baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'foto.file' => 'Berkas foto harus berupa file yang valid.',
            'foto.mimes' => 'Format foto harus berupa JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2048 KB (2 MB).',
        ];
    }
}
