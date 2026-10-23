export interface LaporanIzinTinggalItem {
    id: number;
    bulan: string;
    kategori: string;
    layanan: string;
    disetujui_l: number;
    disetujui_p: number;
    ditolak_l: number;
    ditolak_p: number;
    total_disetujui: number;
    total_ditolak: number;
    total: number;
    keterangan: string | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface LaporanIzinTinggalFormData {
    bulan?: string;
    kategori: string;
    layanan: string;
    disetujui_l: number | string;
    disetujui_p: number | string;
    ditolak_l: number | string;
    ditolak_p: number | string;
    keterangan: string;
}

export interface LaporanIzinTinggalSummary {
    total_disetujui_l: number;
    total_disetujui_p: number;
    total_ditolak_l: number;
    total_ditolak_p: number;
    grand_total_disetujui: number;
    grand_total_ditolak: number;
    grand_total: number;
    total_records: number;
}

export interface LaporanIzinTinggalFilters {
    search?: string;
    kategori?: string;
    layanan?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedLaporanIzinTinggal {
    data: LaporanIzinTinggalItem[];
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
