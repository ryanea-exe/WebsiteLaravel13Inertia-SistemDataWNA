export interface PenerbitanItem {
    id: number;
    no_paspor: string | null;
    nama: string;
    kewarganegaraan: string;
    layanan: string;
    tanggal: string;
    kabupaten: string | null;
    keterangan: string | null;
    created_at: string;
    updated_at: string;
}

export interface PenerbitanFormData {
    no_paspor: string;
    nama: string;
    kewarganegaraan: string;
    layanan: string;
    tanggal: string;
    kabupaten: string;
    keterangan: string;
}

export interface PenerbitanFilters {
    search?: string;
    layanan?: string;
    kewarganegaraan?: string;
    kabupaten?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
}

export interface PaginatedPenerbitan {
    data: PenerbitanItem[];
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

export interface PenerbitanPageProps {
    penerbitan: PaginatedPenerbitan;
    filters: PenerbitanFilters;
    layananOptions: string[];
    kewarganegaraanOptions: string[];
    kabupatenOptions: string[];
    can: {
        create: boolean;
        printList: boolean;
    };
}
