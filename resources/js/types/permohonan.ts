export interface PermohonanItem {
    id: number;
    no_paspor: string | null;
    nama: string;
    kewarganegaraan: string;
    layanan: string;
    tanggal: string;
    kabupaten: string | null;
    permohonan: string | null;
    created_at: string;
    updated_at: string;
}

export interface PermohonanFormData {
    no_paspor: string;
    nama: string;
    kewarganegaraan: string;
    layanan: string;
    tanggal: string;
    kabupaten: string;
    permohonan: string;
}

export interface PermohonanFilters {
    search?: string;
    layanan?: string;
    kewarganegaraan?: string;
    kabupaten?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
}

export interface PaginatedPermohonan {
    data: PermohonanItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

export interface PermohonanPageProps {
    permohonan: PaginatedPermohonan;
    filters: PermohonanFilters;
    layananOptions: string[];
    kewarganegaraanOptions: string[];
    kabupatenOptions: string[];
    can: {
        create: boolean;
        printList: boolean;
    };
}
