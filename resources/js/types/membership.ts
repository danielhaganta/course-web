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

export type PurchasedCourse = {
    title: string;
    slug: string;
};
