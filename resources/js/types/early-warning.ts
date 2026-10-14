import type { PaginatedWna, UrgensiType, WnaItem } from './wna';

export type { PaginatedWna, UrgensiType, WnaItem };

export interface EarlyWarningCounts {
    semua: number;
    merah: number;
    kuning: number;
    biru: number;
    kedaluwarsa: number;
}

export interface EarlyWarningFilters {
    search: string;
    kategori: 'semua' | 'merah' | 'kuning' | 'biru' | 'kedaluwarsa' | string;
    jenis_izin_tinggal: string;
    kewarganegaraan: string;
    sort_by: string;
    sort_order: 'asc' | 'desc';
    per_page: number;
}
