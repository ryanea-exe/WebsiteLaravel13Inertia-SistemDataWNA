import type { GenderType, PaginationLink } from './wna';

export type { GenderType, PaginationLink };

export interface AbgItem {
    id: number;
    no_register: string;
    nama: string;
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
    jenis_kelamin: GenderType;
    kewarganegaraan: string;
    nama_ayah: string | null;
    kewarganegaraan_ayah: string | null;
    nama_ibu: string | null;
    kewarganegaraan_ibu: string | null;
    nomor_telepon: string | null;
    alamat: string | null;
    foto: string | null;
    foto_url: string | null;
    created_at: string;
    updated_at: string;
}

export interface PaginatedAbg {
    data: AbgItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

export interface AbgFilters {
    search: string;
    jenis_kelamin: string;
    kewarganegaraan: string;
    sort_by: string;
    sort_order: 'asc' | 'desc';
    per_page: number;
}

export interface AbgFormData {
    no_register: string;
    nama: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    jenis_kelamin: GenderType | '';
    kewarganegaraan: string;
    nama_ayah: string;
    kewarganegaraan_ayah: string;
    nama_ibu: string;
    kewarganegaraan_ibu: string;
    nomor_telepon: string;
    alamat: string;
    foto: File | null;
    remove_foto?: boolean;
}
