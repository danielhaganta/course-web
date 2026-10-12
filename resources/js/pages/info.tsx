import { Head } from '@inertiajs/react';

type Props = {
    title: string;
};

// Halaman sementara untuk link di footer, isinya menyusul
export default function Info({ title }: Props) {
    return (
        <>
            <Head title={title}>
                <meta
                    head-key="description"
                    name="description"
                    content={`${title} Poeima Academy.`}
                />
            </Head>
            <div className="max-w-2xl space-y-4">
                <h1 className="font-display text-6xl leading-[0.9] font-black uppercase md:text-7xl">
                    {title}
                </h1>
                <p className="text-foreground-soft">
                    Isi halaman ini sedang disiapkan.
                </p>
            </div>
        </>
    );
}
