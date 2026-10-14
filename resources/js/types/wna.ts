export type JenisIzinTinggalType = 'ITAS' | 'ITAP' | 'VOA';
export type GenderType = 'L' | 'P';
export type UrgensiType = 'kedaluwarsa' | 'merah' | 'kuning' | 'biru';

export interface WnaItem {
    id: number;
    no_register: string;
    no_paspor: string;
    nama: string;
    jenis_kelamin: GenderType;
    kewarganegaraan: string;
    jenis_izin_tinggal: JenisIzinTinggalType;
    izin_tinggal_ke: string | null;
    masa_berlaku: string;
    penjamin: string | null;
    alamat_penjamin: string | null;
    nomor_telepon: string | null;
    foto: string | null;
    foto_url: string | null;
    sisa_hari: number;
    urgensi: UrgensiType;
    urgensi_label: string;
    created_at: string;
    updated_at: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedWna {
    data: WnaItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

export interface WnaFilters {
    search: string;
    jenis_izin_tinggal: string;
    kewarganegaraan: string;
    sort_by: string;
    sort_order: 'asc' | 'desc';
    per_page: number;
}

export interface WnaFormData {
    no_register: string;
    no_paspor: string;
    nama: string;
    jenis_kelamin: GenderType | '';
    kewarganegaraan: string;
    jenis_izin_tinggal: JenisIzinTinggalType | '';
    izin_tinggal_ke: string;
    masa_berlaku: string;
    penjamin: string;
    alamat_penjamin: string;
    nomor_telepon: string;
    foto: File | null;
    remove_foto?: boolean;
}
