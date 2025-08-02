// resources/js/types/store.ts
export interface BaseEntity {
    id: number;
    created_at?: string;
    updated_at?: string;
}

export interface ApiResponse<T> {
    data: T;
    message?: string;
    status: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}
