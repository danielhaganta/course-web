export type Plan = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: number;
    duration_days: number;
    can_access_exclusive: boolean;
    can_download: boolean;
};

export type ActiveSubscription = {
    plan_name: string;
    ends_at: string;
};

export type CheckoutResponse = {
    snap_token: string;
    finish_url: string;
    snap_js_url: string;
    client_key: string;
};

export type OrderStatus = 'pending' | 'paid' | 'failed' | 'expired';

export type FinishedOrder = {
    code: string;
    status: OrderStatus;
    amount: number;
    expires_at: string;
    item: {
        type: 'plan' | 'course';
        name: string;
        slug: string;
    };
};

export type OrderHistoryItem = {
    code: string;
    item_name: string;
    amount: number;
    payment_method: string | null;
    status: OrderStatus;
    status_label: string;
    created_at: string;
    // Hanya untuk order pending yang belum kedaluwarsa
    payment: Pick<CheckoutResponse, 'snap_token' | 'finish_url'> | null;
};

export type SnapConfig = Pick<CheckoutResponse, 'snap_js_url' | 'client_key'>;

export type PurchasedCourse = {
    title: string;
    slug: string;
};

export type DashboardCourse = {
    title: string;
    slug: string;
    thumbnail_url: string | null;
    lessons_count: number;
    completed_lessons_count: number;
};

export type ContinueLearning = DashboardCourse & {
    // Materi pertama yang belum selesai
    next_lesson_id: number | null;
};
