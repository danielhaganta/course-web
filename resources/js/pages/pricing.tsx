import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import CheckoutController from '@/actions/App/Http/Controllers/CheckoutController';
import { Badge } from '@/components/ui/badge';
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

    return (
        <>
            <Head title="Pricing">
                <meta
                    head-key="description"
                    name="description"
                    content="Choose a Poeima Academy plan to unlock every color grading course, or buy a single course."
                />
            </Head>

            <section className="mx-auto max-w-3xl text-center">
                <h1 className="font-display text-[clamp(56px,8vw,96px)] leading-[0.92] font-extrabold uppercase">
                    Choose your plan
                </h1>
                <p className="mx-auto mt-5 max-w-[560px] text-lg leading-relaxed text-foreground-soft">
                    A subscription unlocks every course. Access stays active
                    until your subscription ends.
                </p>
                <BillingToggle
                    billing={billing}
                    onChange={setBilling}
                    savingsPercent={savingsPercent}
                />
            </section>

            {visiblePlans.length === 0 ? (
                <p className="mx-auto mt-12 max-w-[1000px] rounded-md border border-dashed p-8 text-center text-muted-foreground">
                    No plans available yet.
                </p>
            ) : (
                <section
                    aria-label="Subscription plans"
                    className="mx-auto mt-12 grid max-w-[1000px] gap-6 md:grid-cols-2"
                >
                    {visiblePlans.map((plan) => (
                        <PlanCard
                            key={plan.id}
                            plan={plan}
                            isHighlighted={isComplete(plan)}
                            isGuest={!auth.user}
                        />
                    ))}
                </section>
            )}

            <section className="mx-auto mt-6 flex max-w-[1000px] flex-wrap items-center justify-between gap-5 rounded-md border border-dashed border-foreground/25 px-6 py-7 sm:px-8">
                <div>
                    <h2 className="text-xl font-bold">Only need one course?</h2>
                    <p className="mt-1.5 text-muted-foreground">
                        Buy it on its own, pay once, and keep it forever. Prices
                        are on each course page.
                    </p>
                </div>
                <Button asChild variant="outline">
                    <Link href={coursesIndex()}>Browse courses</Link>
                </Button>
            </section>

            <ComparisonTable plans={visiblePlans} maxDevices={maxDevices} />
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
            className="mt-9 inline-flex gap-1 rounded-md border bg-secondary p-1"
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

function PlanCard({
    plan,
    isHighlighted,
    isGuest,
}: {
    plan: Plan;
    isHighlighted: boolean;
    isGuest: boolean;
}) {
    const benefits = [
        { label: 'All regular course videos', isIncluded: true },
        { label: 'Saved learning progress', isIncluded: true },
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
    ];

    return (
        <div
            className={cn(
                'flex flex-col rounded-md bg-secondary p-7 sm:p-9',
                isHighlighted ? 'border-2 border-foreground' : 'border',
            )}
        >
            <div className="flex items-center justify-between gap-3">
                <h2 className="text-2xl font-bold">{plan.name}</h2>
                {isHighlighted && <Badge>Most complete</Badge>}
            </div>
            {plan.description && (
                <p className="mt-2 text-muted-foreground">{plan.description}</p>
            )}
            <p className="mt-7 font-display text-[56px] leading-none font-extrabold">
                {formatRupiah(plan.price)}
            </p>
            <p className="mt-1.5 text-sm text-muted-foreground">
                {periodLabel(plan)}
            </p>
            <ul className="mt-7 flex-1 space-y-3 text-[15px]">
                {benefits.map((benefit) => (
                    <li
                        key={benefit.label}
                        className={cn(
                            !benefit.isIncluded && 'text-foreground/40',
                        )}
                    >
                        {benefit.label}
                    </li>
                ))}
            </ul>
            <div className="mt-8">
                {isGuest ? (
                    <Button
                        asChild
                        size="lg"
                        variant={isHighlighted ? 'default' : 'outline'}
                        className="w-full"
                    >
                        <Link href={register()}>Choose {plan.name}</Link>
                    </Button>
                ) : (
                    <CheckoutButton plan={plan} isHighlighted={isHighlighted} />
                )}
            </div>
        </div>
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
                {isPreparing ? 'Preparing payment...' : `Choose ${plan.name}`}
            </Button>
            {error && (
                <p role="alert" className="text-sm text-destructive">
                    {error}
                </p>
            )}
        </div>
    );
}

function ComparisonTable({
    plans,
    maxDevices,
}: {
    plans: Plan[];
    maxDevices: number;
}) {
    const yesNo = (value: boolean) => (value ? 'Yes' : 'No');
    const rows = [
        {
            label: 'Courses you can watch',
            single: 'The purchased course',
            plan: () => 'All',
        },
        {
            label: 'Access period',
            single: 'Lifetime',
            plan: () => 'While subscribed',
        },
        {
            label: 'Exclusive lessons',
            single: 'No',
            plan: (plan: Plan) => yesNo(plan.can_access_exclusive),
        },
        {
            label: 'Practice file downloads',
            single: 'No',
            plan: (plan: Plan) => yesNo(plan.can_download),
        },
        {
            label: 'Devices',
            single: pluralize(maxDevices, 'device'),
            plan: () => pluralize(maxDevices, 'device'),
        },
    ];

    return (
        <section
            aria-labelledby="comparison-title"
            className="mx-auto mt-24 max-w-[1000px]"
        >
            <h2
                id="comparison-title"
                className="font-display text-5xl font-extrabold uppercase"
            >
                Full comparison
            </h2>
            {/* Di HP tabel bisa digeser ke samping */}
            <div className="mt-6 overflow-x-auto rounded-md border">
                <table className="w-full min-w-[620px] border-collapse text-left text-[15px]">
                    <thead className="bg-secondary">
                        <tr>
                            <th scope="col" className="px-5 py-4 font-semibold">
                                Feature
                            </th>
                            <th scope="col" className="px-5 py-4 font-semibold">
                                Single purchase
                            </th>
                            {plans.map((plan) => (
                                <th
                                    key={plan.id}
                                    scope="col"
                                    className="px-5 py-4 font-semibold"
                                >
                                    {plan.name}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((row) => (
                            <tr key={row.label} className="border-t">
                                <th
                                    scope="row"
                                    className="px-5 py-4 font-normal text-foreground-soft"
                                >
                                    {row.label}
                                </th>
                                <td className="px-5 py-4">{row.single}</td>
                                {plans.map((plan) => (
                                    <td key={plan.id} className="px-5 py-4">
                                        {row.plan(plan)}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <p className="mt-5 text-sm text-muted-foreground">
                Pay with QRIS, bank virtual account, GoPay, or ShopeePay.
            </p>
        </section>
    );
}
