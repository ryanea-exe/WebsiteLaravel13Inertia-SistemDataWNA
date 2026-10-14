export interface RealisasiPnbpItem {
    id: number;
    bulan: string;
    target: string | number;
    realisasi: string | number;
    selisih: number;
    persentase: number;
    keterangan: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface RealisasiPnbpFormData {
    bulan: string;
    target: string | number;
    realisasi: string | number;
    keterangan: string;
}

export interface RealisasiPnbpFilters {
    search?: string;
    tahun?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
}

export interface RealisasiPnbpSummary {
    total_target: number;
    total_realisasi: number;
    total_selisih: number;
    persentase_total: number;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedRealisasiPnbp {
    data: RealisasiPnbpItem[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export interface RealisasiPnbpPageProps {
    realisasiPnbp: PaginatedRealisasiPnbp;
    filters: RealisasiPnbpFilters;
    tahunOptions: string[];
    summary: RealisasiPnbpSummary;
    can: {
        create: boolean;
        printList: boolean;
    };
}
