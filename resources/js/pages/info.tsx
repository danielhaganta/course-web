import { Head } from '@inertiajs/react';

type Props = {
    title: string;
    placeholder: string;
};

// Halaman informasi dari footer, isinya sementara placeholder
export default function Info({ title, placeholder }: Props) {
    return (
        <>
            <Head title={title}>
                <meta
                    head-key="description"
                    name="description"
                    content={`${title} Poeima Academy.`}
                />
            </Head>
            <article className="max-w-2xl">
                <h1 className="font-display text-[clamp(48px,7vw,80px)] leading-[0.9] font-black uppercase">
                    {title}
                </h1>
                <p className="mt-8 text-lg leading-[1.7] text-foreground-soft">
                    {placeholder}
                </p>
            </article>
        </>
    );
}
