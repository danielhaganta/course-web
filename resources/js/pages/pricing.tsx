import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { CSSProperties, ReactNode } from 'react';
import CheckoutController from '@/actions/App/Http/Controllers/CheckoutController';
import { Button } from '@/components/ui/button';
import { useCheckout } from '@/hooks/use-checkout';
import { formatRupiah } from '@/lib/currency';
import { cn, pluralize } from '@/lib/utils';
import { register } from '@/routes';
import { index as coursesIndex } from '@/routes/courses';
import type { Plan } from '@/types';

type Billing = 'monthly' | 'yearly';

type Props = {
    plans: Plan[];
    maxDevices: number;
};

// Plan dikelompokkan dari durasinya, bukan dari nama
const isYearly = (plan: Plan) => plan.duration_days >= 365;

// Paket bulanan dan tahunan dipasangkan lewat benefitnya
const tierKey = (plan: Plan) =>
    `${plan.can_access_exclusive}-${plan.can_download}`;

const isComplete = (plan: Plan) =>
    plan.can_access_exclusive && plan.can_download;

/**
 * Persentase hemat terbesar paket tahunan dibanding 12 kali bulanan pada tingkat yang sama.
 * Null kalau tidak ada paket tahunan yang lebih murah.
 */
function yearlySavingsPercent(plans: Plan[]): number | null {
    const savings = plans.filter(isYearly).flatMap((yearly) => {
        const monthly = plans.find(
            (plan) =>
                !isYearly(plan) &&
                plan.duration_days === 30 &&
                tierKey(plan) === tierKey(yearly),
        );

        if (!monthly || yearly.price >= monthly.price * 12) {
            return [];
        }

        return [Math.round((1 - yearly.price / (monthly.price * 12)) * 100)];
    });

    return savings.length > 0 ? Math.max(...savings) : null;
}

function periodLabel(plan: Plan): string {
    if (isYearly(plan)) {
        return 'per year';
    }

    return plan.duration_days === 30
        ? 'per month'
        : `per ${plan.duration_days} days`;
}

export default function Pricing({ plans, maxDevices }: Props) {
    const { auth } = usePage().props;
    const [billing, setBilling] = useState<Billing>('monthly');
    const visiblePlans = plans.filter(
        (plan) => isYearly(plan) === (billing === 'yearly'),
    );
    const savingsPercent = yearlySavingsPercent(plans);
    const devices = `Up to ${pluralize(maxDevices, 'device')}`;

    return (
        <>
            <Head title="Pricing">
                <meta
                    head-key="description"
                    name="description"
                    content="Choose a Poeima Academy plan to unlock every color grading course, or buy a single course."
                />
            </Head>

            <section className="flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
                <div className="max-w-2xl">
                    <h1 className="font-display text-[clamp(56px,8vw,96px)] leading-[0.92] font-extrabold uppercase">
                        Choose your plan
                    </h1>
                    <p className="mt-5 max-w-[560px] text-lg leading-relaxed text-foreground-soft">
                        A subscription unlocks every course. Access stays active
                        until your subscription ends.
                    </p>
                </div>
                <BillingToggle
                    billing={billing}
                    onChange={setBilling}
                    savingsPercent={savingsPercent}
                />
            </section>

            {visiblePlans.length === 0 && (
                <p className="mt-12 rounded-md border border-dashed p-8 text-center text-muted-foreground">
                    No plans available yet.
                </p>
            )}

            {/* Satu kolom per penawaran, termasuk beli satuan */}
            <section
                aria-label="Plans and single purchase"
                style={
                    {
                        '--offer-count': visiblePlans.length + 1,
                    } as CSSProperties
                }
                className="mt-12 grid border-t md:grid-cols-[repeat(var(--offer-count),minmax(0,1fr))]"
            >
                {visiblePlans.map((plan) => {
                    const isHighlighted = isComplete(plan);

                    return (
                        <OfferColumn
                            key={plan.id}
                            name={plan.name}
                            description={plan.description}
                            price={formatRupiah(plan.price)}
                            period={periodLabel(plan)}
                            isHighlighted={isHighlighted}
                            features={[
                                { label: 'Every course', isIncluded: true },
                                {
                                    label: 'Access while subscribed',
                                    isIncluded: true,
                                },
                                {
                                    label: plan.can_access_exclusive
                                        ? 'Exclusive lessons'
                                        : 'No exclusive lessons',
                                    isIncluded: plan.can_access_exclusive,
                                },
                                {
                                    label: plan.can_download
                                        ? 'Practice file downloads'
                                        : 'No practice file downloads',
                                    isIncluded: plan.can_download,
                                },
                                { label: devices, isIncluded: true },
                            ]}
                            action={
                                auth.user ? (
                                    <CheckoutButton
                                        plan={plan}
                                        isHighlighted={isHighlighted}
                                    />
                                ) : (
                                    <Button
                                        asChild
                                        size="lg"
                                        variant={
                                            isHighlighted
                                                ? 'default'
                                                : 'outline'
                                        }
                                        className="w-full"
                                    >
                                        <Link href={register()}>
                                            Choose {plan.name}
                                        </Link>
                                    </Button>
                                )
                            }
                        />
                    );
                })}

                <OfferColumn
                    name="Single course"
                    description="Only need one course? Buy it on its own."
                    price="Pay once"
                    period="Price on each course page"
                    features={[
                        {
                            label: 'One course of your choice',
                            isIncluded: true,
                        },
                        { label: 'Yours forever', isIncluded: true },
                        { label: 'No exclusive lessons', isIncluded: false },
                        {
                            label: 'No practice file downloads',
                            isIncluded: false,
                        },
                        { label: devices, isIncluded: true },
                    ]}
                    action={
                        <Button
                            asChild
                            size="lg"
                            variant="outline"
                            className="w-full"
                        >
                            <Link href={coursesIndex()}>Browse courses</Link>
                        </Button>
                    }
                />
            </section>

            <p className="mt-6 text-sm text-muted-foreground">
                Every option saves your learning progress. Pay with QRIS, bank
                virtual account, GoPay, or ShopeePay.
            </p>
        </>
    );
}

function BillingToggle({
    billing,
    onChange,
    savingsPercent,
}: {
    billing: Billing;
    onChange: (billing: Billing) => void;
    savingsPercent: number | null;
}) {
    const options: { value: Billing; label: string }[] = [
        { value: 'monthly', label: 'Monthly' },
        {
            value: 'yearly',
            label:
                savingsPercent !== null
                    ? `Yearly · save ${savingsPercent}%`
                    : 'Yearly',
        },
    ];

    return (
        <div
            role="group"
            aria-label="Billing period"
            className="inline-flex gap-1 self-start rounded-md border bg-secondary p-1 md:self-auto"
        >
            {options.map((option) => {
                const isActive = billing === option.value;

                return (
                    <button
                        key={option.value}
                        type="button"
                        aria-pressed={isActive}
                        onClick={() => onChange(option.value)}
                        className={cn(
                            'min-h-11 rounded-[4px] px-4 text-sm font-bold transition-colors outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring sm:px-5',
                            isActive
                                ? 'bg-foreground text-background'
                                : 'text-foreground hover:bg-foreground/10',
                        )}
                    >
                        {option.label}
                    </button>
                );
            })}
        </div>
    );
}

type Feature = {
    label: string;
    isIncluded: boolean;
};

/**
 * Satu kolom penawaran. Di desktop baris-barisnya disejajarkan antar kolom lewat subgrid:
 * 3 baris (nama, harga, tombol) + 5 baris fitur = row-span-8. Jumlah fitur harus 5.
 */
function OfferColumn({
    name,
    description,
    price,
    period,
    features,
    action,
    isHighlighted = false,
}: {
    name: string;
    description: string | null;
    price: string;
    period: string;
    features: Feature[];
    action: ReactNode;
    isHighlighted?: boolean;
}) {
    return (
        <article
            className={cn(
                'flex flex-col border-b px-5 py-8 md:row-span-8 md:grid md:grid-rows-subgrid md:border-b-0 md:border-l md:px-8 md:first:border-l-0',
                isHighlighted && 'bg-secondary',
            )}
        >
            <div>
                <h2 className="text-2xl font-bold">{name}</h2>
                {description && (
                    <p className="mt-2 text-muted-foreground">{description}</p>
                )}
            </div>
            <div className="mt-7">
                <p className="font-display text-[56px] leading-none font-extrabold tabular-nums">
                    {price}
                </p>
                <p className="mt-1.5 text-sm text-muted-foreground">{period}</p>
            </div>
            <div className="mt-7">{action}</div>
            <ul className="mt-8 space-y-3 text-[15px] md:row-span-5 md:grid md:grid-rows-subgrid md:space-y-0 md:gap-y-3">
                {features.map((feature) => (
                    <li
                        key={feature.label}
                        className={cn(
                            !feature.isIncluded && 'text-foreground/40',
                        )}
                    >
                        {feature.label}
                    </li>
                ))}
            </ul>
        </article>
    );
}

function CheckoutButton({
    plan,
    isHighlighted,
}: {
    plan: Plan;
    isHighlighted: boolean;
}) {
    const { startCheckout, isPreparing, error } = useCheckout();

    return (
        <div className="space-y-2">
            <Button
                size="lg"
                variant={isHighlighted ? 'default' : 'outline'}
                className="w-full"
                disabled={isPreparing}
                onClick={() =>
                    startCheckout(CheckoutController.plan.url(plan.slug))
                }
            >
                {isPreparing ? 'Preparing payment…' : `Choose ${plan.name}`}
            </Button>
            {error && (
                <p role="alert" className="text-sm text-destructive">
                    {error}
                </p>
            )}
        </div>
    );
}
