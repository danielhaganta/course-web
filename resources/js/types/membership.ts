export type Plan = {
    id: number;
    name: string;
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

export type PurchasedCourse = {
    title: string;
    slug: string;
};
