import { Head, Link, usePage } from '@inertiajs/react';
import { Check, X } from 'lucide-react';
import { useState } from 'react';
import CheckoutController from '@/actions/App/Http/Controllers/CheckoutController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useCheckout } from '@/hooks/use-checkout';
import { formatRupiah } from '@/lib/currency';
import { cn } from '@/lib/utils';
import { register } from '@/routes';
import type { Plan } from '@/types';

type Billing = 'monthly' | 'yearly';

type Props = {
    plans: Plan[];
};

// Plan dikelompokkan dari durasinya, bukan dari nama
const isYearly = (plan: Plan) => plan.duration_days >= 365;

export default function Pricing({ plans }: Props) {
    const { auth } = usePage().props;
    const [billing, setBilling] = useState<Billing>('monthly');
    const visiblePlans = plans.filter(
        (plan) => isYearly(plan) === (billing === 'yearly'),
    );

    return (
        <>
            <Head title="Paket membership" />
            <div className="space-y-8">
                <div className="space-y-2 text-center">
                    <h1 className="text-3xl font-semibold tracking-tight">
                        Paket membership
                    </h1>
                    <p className="text-muted-foreground">
                        Semua paket membuka semua course.
                    </p>
                </div>

                <ToggleGroup
                    type="single"
                    variant="outline"
                    value={billing}
                    onValueChange={(value) => {
                        if (value === 'monthly' || value === 'yearly') {
                            setBilling(value);
                        }
                    }}
                    className="mx-auto"
                    aria-label="Periode tagihan"
                >
                    <ToggleGroupItem value="monthly" className="px-4">
                        Bulanan
                    </ToggleGroupItem>
                    <ToggleGroupItem value="yearly" className="px-4">
                        Tahunan
                    </ToggleGroupItem>
                </ToggleGroup>

                {visiblePlans.length === 0 ? (
                    <p className="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
                        Belum ada paket yang tersedia.
                    </p>
                ) : (
                    <div className="mx-auto grid max-w-3xl gap-6 md:grid-cols-2">
                        {visiblePlans.map((plan) => (
                            <PlanCard
                                key={plan.id}
                                plan={plan}
                                isGuest={!auth.user}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

function PlanCard({ plan, isGuest }: { plan: Plan; isGuest: boolean }) {
    const benefits = [
        { label: 'Akses semua course', isIncluded: true },
        { label: 'Materi exclusive', isIncluded: plan.can_access_exclusive },
        { label: 'Download file latihan', isIncluded: plan.can_download },
    ];

    return (
        <Card>
            <CardHeader>
                <CardTitle>{plan.name}</CardTitle>
                {plan.description && (
                    <CardDescription>{plan.description}</CardDescription>
                )}
            </CardHeader>
            <CardContent className="space-y-6">
                <p>
                    <span className="text-3xl font-semibold">
                        {formatRupiah(plan.price)}
                    </span>{' '}
                    <span className="text-muted-foreground">
                        / {plan.duration_days} hari
                    </span>
                </p>
                <ul className="space-y-2 text-sm">
                    {benefits.map((benefit) => (
                        <li
                            key={benefit.label}
                            className={cn(
                                'flex items-center gap-2',
                                !benefit.isIncluded && 'text-muted-foreground',
                            )}
                        >
                            {benefit.isIncluded ? (
                                <Check
                                    className="size-4 text-primary"
                                    aria-hidden
                                />
                            ) : (
                                <X className="size-4" aria-hidden />
                            )}
                            <span
                                className={cn(
                                    !benefit.isIncluded && 'line-through',
                                )}
                            >
                                {benefit.label}
                            </span>
                            {!benefit.isIncluded && (
                                <span className="sr-only">
                                    (tidak termasuk)
                                </span>
                            )}
                        </li>
                    ))}
                </ul>
            </CardContent>
            <CardFooter className="mt-auto">
                {isGuest ? (
                    <Button asChild className="w-full">
                        <Link href={register()}>Pilih paket</Link>
                    </Button>
                ) : (
                    <CheckoutButton plan={plan} />
                )}
            </CardFooter>
        </Card>
    );
}

function CheckoutButton({ plan }: { plan: Plan }) {
    const { startCheckout, isPreparing, error } = useCheckout();

    return (
        <div className="w-full space-y-2">
            <Button
                className="w-full"
                disabled={isPreparing}
                onClick={() =>
                    startCheckout(CheckoutController.plan.url(plan.slug))
                }
            >
                {isPreparing ? 'Menyiapkan pembayaran...' : 'Pilih paket'}
            </Button>
            {error && (
                <p role="alert" className="text-sm text-destructive">
                    {error}
                </p>
            )}
        </div>
    );
}
