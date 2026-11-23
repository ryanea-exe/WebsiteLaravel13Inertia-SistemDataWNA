import type { PaginatedWna, UrgensiType, WnaItem } from './wna';

export type { PaginatedWna, UrgensiType, WnaItem };

export interface EarlyWarningWnaCounts {
    semua: number;
    merah: number;
    kuning: number;
    biru: number;
    kedaluwarsa: number;
}

export type EarlyWarningCounts = EarlyWarningWnaCounts;

export interface EarlyWarningWnaFilters {
    search: string;
    kategori: 'semua' | 'merah' | 'kuning' | 'biru' | 'kedaluwarsa' | string;
    jenis_izin_tinggal: string;
    kewarganegaraan: string;
    sort_by: string;
    sort_order: 'asc' | 'desc';
    per_page: number;
}

export type EarlyWarningFilters = EarlyWarningWnaFilters;

