import { Form, Head, Link } from '@inertiajs/react';
import { CircleCheck, CircleX, Clock, TimerOff } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import CheckoutController from '@/actions/App/Http/Controllers/CheckoutController';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/currency';
import { formatDateTime } from '@/lib/date';
import { cn } from '@/lib/utils';
import { dashboard, pricing } from '@/routes';
import { index as coursesIndex, show as showCourse } from '@/routes/courses';
import type { FinishedOrder, OrderStatus } from '@/types';

type Props = {
    order: FinishedOrder;
};

const STATUS_DISPLAY: Record<
    OrderStatus,
    { title: string; description: string; icon: LucideIcon; tone: string }
> = {
    paid: {
        title: 'Payment successful',
        description: 'Your access is now active.',
        icon: CircleCheck,
        tone: 'text-foreground',
    },
    pending: {
        title: 'Awaiting payment',
        description:
            'Complete the payment by following the instructions. Your access turns on automatically once we receive it.',
        icon: Clock,
        tone: 'text-foreground-soft',
    },
    failed: {
        title: 'Payment failed',
        description: "The payment couldn't be processed. Please try again.",
        icon: CircleX,
        tone: 'text-destructive-foreground',
    },
    expired: {
        title: 'Payment expired',
        description:
            'The payment deadline has passed. Please place a new order.',
        icon: TimerOff,
        tone: 'text-muted-foreground',
    },
};

export default function CheckoutFinish({ order }: Props) {
    const display = STATUS_DISPLAY[order.status];
    const Icon = display.icon;

    return (
        <>
            <Head title={display.title} />
            <div className="mx-auto flex w-full max-w-lg flex-col items-center gap-6 p-4 py-10 text-center">
                <Icon className={cn('size-12', display.tone)} aria-hidden />
                <div className="space-y-2">
                    <h1 className="font-display text-5xl leading-[0.92] font-extrabold uppercase">
                        {display.title}
                    </h1>
                    <p className="text-muted-foreground">
                        {display.description}
                    </p>
                </div>

                <dl className="w-full divide-y rounded-md border bg-card text-left text-sm">
                    <OrderRow label="Item" value={order.item.name} />
                    <OrderRow
                        label="Total"
                        value={formatRupiah(order.amount)}
                    />
                    <OrderRow label="Order code" value={order.code} />
                    {order.status === 'pending' && (
                        <OrderRow
                            label="Pay before"
                            value={formatDateTime(order.expires_at)}
                        />
                    )}
                </dl>

                <OrderActions order={order} />
            </div>
        </>
    );
}

function OrderRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4 p-3">
            <dt className="text-muted-foreground">{label}</dt>
            <dd className="text-right font-medium break-all">{value}</dd>
        </div>
    );
}

function OrderActions({ order }: Props) {
    const isCourse = order.item.type === 'course';

    if (order.status === 'pending') {
        return (
            <Form
                {...CheckoutController.refreshStatus.form(order.code)}
                options={{ preserveScroll: true }}
                className="w-full"
            >
                {({ processing }) => (
                    <Button className="w-full" disabled={processing}>
                        {processing
                            ? 'Checking status...'
                            : 'Check payment status'}
                    </Button>
                )}
            </Form>
        );
    }

    if (order.status === 'paid') {
        return (
            <div className="flex w-full flex-col gap-2 sm:flex-row">
                <Button asChild className="flex-1">
                    <Link
                        href={
                            isCourse
                                ? showCourse(order.item.slug)
                                : coursesIndex()
                        }
                    >
                        {isCourse ? 'Start learning' : 'Browse all courses'}
                    </Link>
                </Button>
                <Button asChild variant="outline" className="flex-1">
                    <Link href={dashboard()}>Go to dashboard</Link>
                </Button>
            </div>
        );
    }

    return (
        <Button asChild className="w-full">
            <Link href={isCourse ? showCourse(order.item.slug) : pricing()}>
                Try again
            </Link>
        </Button>
    );
}
