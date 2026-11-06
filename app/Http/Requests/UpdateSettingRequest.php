<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', Setting::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:100'],
            'instansi_name' => ['nullable', 'string', 'max:150'],
            'app_description' => ['nullable', 'string', 'max:255'],
            'primary_logo' => ['nullable', 'in:1,2,3'],
            'app_icon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_icon' => ['nullable', 'boolean'],
            'app_logo_1' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo_1' => ['nullable', 'boolean'],
            'app_logo_2' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo_2' => ['nullable', 'boolean'],
            'app_logo_3' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo_3' => ['nullable', 'boolean'],
            'show_login_logo' => ['nullable', 'boolean'],
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
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'app_name.max' => 'Nama aplikasi maksimal 100 karakter.',
            'instansi_name.max' => 'Nama instansi maksimal 150 karakter.',
            'app_description.max' => 'Deskripsi aplikasi maksimal 255 karakter.',
            'primary_logo.in' => 'Pilihan logo utama harus antara Logo 1, Logo 2, atau Logo 3.',
            'app_icon.file' => 'Berkas ikon aplikasi harus berupa file yang valid.',
            'app_icon.mimes' => 'Ikon aplikasi harus berupa file gambar PNG, JPG, JPEG, atau WEBP.',
            'app_icon.max' => 'Ukuran ikon aplikasi maksimal 1024 KB (1 MB).',
            'app_logo_1.file' => 'Berkas Logo 1 harus berupa file yang valid.',
            'app_logo_1.mimes' => 'Logo 1 harus berupa file gambar PNG, JPG, JPEG, atau WEBP.',
            'app_logo_1.max' => 'Ukuran Logo 1 maksimal 1024 KB (1 MB).',
            'app_logo_2.file' => 'Berkas Logo 2 harus berupa file yang valid.',
            'app_logo_2.mimes' => 'Logo 2 harus berupa file gambar PNG, JPG, JPEG, atau WEBP.',
            'app_logo_2.max' => 'Ukuran Logo 2 maksimal 1024 KB (1 MB).',
            'app_logo_3.file' => 'Berkas Logo 3 harus berupa file yang valid.',
            'app_logo_3.mimes' => 'Logo 3 harus berupa file gambar PNG, JPG, JPEG, atau WEBP.',
            'app_logo_3.max' => 'Ukuran Logo 3 maksimal 1024 KB (1 MB).',
        ];
    }
}
