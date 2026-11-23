import type { GenderType, PaginationLink } from './wna';

export interface AbgEarlyWarningItem {
    id: number;
    no_register: string;
    nama: string;
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
    jenis_kelamin: GenderType;
    kewarganegaraan: string;
    kabupaten: string | null;
    nama_ayah: string | null;
    kewarganegaraan_ayah: string | null;
    nama_ibu: string | null;
    kewarganegaraan_ibu: string | null;
    nomor_telepon: string | null;
    alamat: string | null;
    foto: string | null;
    foto_url: string | null;
    umur?: string;
    target_milestone?: string | null;
    sisa_hari: number;
    urgensi: 'kurang_18' | 'kurang_21' | 'lewat_21';
    status_label?: string;
    sisa_waktu_label?: string;
    created_at: string;
    updated_at: string;
}

export interface PaginatedAbgEarlyWarning {
    data: AbgEarlyWarningItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

export interface EarlyWarningAbgCounts {
    semua: number;
    kurang_18: number;
    kurang_21: number;
    lewat_21: number;
}

export interface EarlyWarningAbgFilters {
    search: string;
    kategori: string;
    jenis_kelamin?: string;
    kewarganegaraan?: string;
    kabupaten?: string;
    sort_by: string;
    sort_order: 'asc' | 'desc';
    per_page: number;
}

