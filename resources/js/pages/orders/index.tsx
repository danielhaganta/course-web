import { Head, Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import type { ComponentProps } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useCheckout } from '@/hooks/use-checkout';
import { formatRupiah } from '@/lib/currency';
import { finish } from '@/routes/checkout';
import { index as ordersIndex } from '@/routes/orders';
import type { OrderHistoryItem, OrderStatus, SnapConfig } from '@/types';

type Props = {
    orders: OrderHistoryItem[];
    snap: SnapConfig;
};

const STATUS_BADGE: Record<
    OrderStatus,
    ComponentProps<typeof Badge>['variant']
> = {
    paid: 'default',
    pending: 'secondary',
    failed: 'destructive',
    expired: 'outline',
};

export default function OrdersIndex({ orders, snap }: Props) {
    return (
        <>
            <Head title="Riwayat pembayaran" />
            <div className="flex flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <h1 className="font-display text-[clamp(40px,5vw,56px)] leading-[0.92] font-extrabold uppercase">
                    Riwayat pembayaran
                </h1>

                {orders.length === 0 ? (
                    <p className="rounded-md border border-dashed p-6 text-center text-sm text-muted-foreground">
                        Belum ada pembayaran.
                    </p>
                ) : (
                    <ul className="divide-y rounded-md border bg-card">
                        {orders.map((order) => (
                            <OrderRow
                                key={order.code}
                                order={order}
                                snap={snap}
                            />
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

function OrderRow({
    order,
    snap,
}: {
    order: OrderHistoryItem;
    snap: SnapConfig;
}) {
    const { continuePayment, isPreparing, error } = useCheckout();
    const payment = order.payment;

    return (
        <li className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
            <Link
                href={finish(order.code)}
                className="group flex min-w-0 flex-1 items-center gap-3 rounded-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                <div className="min-w-0 flex-1 space-y-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="font-medium group-hover:underline">
                            {order.item_name}
                        </span>
                        <Badge variant={STATUS_BADGE[order.status]}>
                            {order.status_label}
                        </Badge>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        {order.code} · {order.payment_method ?? '-'} ·{' '}
                        {formatDateTime(order.created_at)}
                    </p>
                </div>
                <span className="font-medium tabular-nums">
                    {formatRupiah(order.amount)}
                </span>
                <ChevronRight
                    className="size-4 shrink-0 text-muted-foreground"
                    aria-hidden
                />
            </Link>

            {payment && (
                <div className="space-y-1 sm:w-48">
                    <Button
                        size="sm"
                        className="w-full"
                        disabled={isPreparing}
                        onClick={() => continuePayment({ ...payment, ...snap })}
                    >
                        {isPreparing
                            ? 'Membuka pembayaran...'
                            : 'Lanjutkan pembayaran'}
                    </Button>
                    {error && (
                        <p role="alert" className="text-xs text-destructive">
                            {error}
                        </p>
                    )}
                </div>
            )}
        </li>
    );
}

function formatDateTime(isoDate: string): string {
    return new Date(isoDate).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone: 'Asia/Jakarta',
    });
}

OrdersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Riwayat pembayaran',
            href: ordersIndex(),
        },
    ],
};
