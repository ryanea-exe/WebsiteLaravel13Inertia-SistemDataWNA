export interface NotificationItem {
    id: number;
    nama: string;
    no_paspor: string;
    no_register: string;
    kewarganegaraan: string;
    masa_berlaku: string | null;
    lewat_hari: number;
    keterangan: string;
}

export interface NotificationsPayload {
    count: number;
    items: NotificationItem[];
}
