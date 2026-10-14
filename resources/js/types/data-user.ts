export interface UserItem {
    id: number;
    nama: string;
    username: string;
    email: string | null;
    role: 'admin' | 'user';
    role_label: string;
    foto: string | null;
    foto_url: string | null;
    terakhir_login: string | null;
    created_at: string;
}

export interface UserFormData {
    nama: string;
    username: string;
    email: string;
    role: 'admin' | 'user';
    password?: string;
    password_confirmation?: string;
    foto?: File | null;
    remove_foto?: boolean;
}

export interface UserFilters {
    search?: string;
    role?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
}

export interface PaginatedUsers {
    data: UserItem[];
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

export interface RoleOption {
    value: string;
    label: string;
}

export interface DataUserPageProps {
    users: PaginatedUsers;
    filters: UserFilters;
    roleOptions: RoleOption[];
    can: {
        create: boolean;
    };
    authUserId: number;
}
