import { Head } from '@inertiajs/react';
import FootagePlaceholder from '@/components/footage-placeholder';

// Placeholder, ganti dengan data pengajar asli
const INSTRUCTOR = {
    name: '[Nama Pengajar]',
    photoSrc: null,
    bio: '[Bio singkat: latar belakang sebagai colorist, jenis proyek yang pernah dikerjakan, dan alasan membuka kelas ini.]',
    projects: [
        { title: '[Judul proyek]', meta: '[Klien · tahun]' },
        { title: '[Judul proyek]', meta: '[Klien · tahun]' },
        { title: '[Judul proyek]', meta: '[Klien · tahun]' },
    ],
};

export default function Instructor() {
    return (
        <>
            <Head title="Pengajar">
                <meta
                    head-key="description"
                    name="description"
                    content="Kenali pengajar Poeima Academy: latar belakangnya sebagai colorist dan proyek yang pernah dikerjakan."
                />
            </Head>

            <section
                aria-labelledby="instructor-title"
                className="flex flex-col lg:h-dvh lg:min-h-[640px] lg:flex-row"
            >
                <div className="relative h-[60svh] lg:h-auto lg:flex-1">
                    {INSTRUCTOR.photoSrc ? (
                        <img
                            src={INSTRUCTOR.photoSrc}
                            alt={`Foto ${INSTRUCTOR.name}`}
                            className="absolute inset-0 size-full object-cover"
                        />
                    ) : (
                        <>
                            <FootagePlaceholder
                                look="graded"
                                tone="saturate(.55) brightness(.8)"
                                className="absolute inset-0"
                            />
                            <span className="absolute bottom-6 left-6 rounded-[3px] bg-background/50 px-2 py-1.5 text-xs font-medium lg:bottom-8 lg:left-10">
                                [Foto pengajar]
                            </span>
                        </>
                    )}
                </div>

                <div className="flex flex-col justify-end px-6 py-12 lg:flex-1 lg:px-14 lg:pt-28 lg:pb-12">
                    <p className="text-[13px] font-semibold tracking-[0.22em] uppercase">
                        Pengajar
                    </p>
                    <h1
                        id="instructor-title"
                        className="mt-4 max-w-[10ch] font-display text-[clamp(56px,6vw,104px)] leading-[0.85] font-black uppercase"
                    >
                        {INSTRUCTOR.name}
                    </h1>
                    <p className="mt-7 max-w-[520px] text-[17px] leading-[1.7] text-foreground-soft">
                        {INSTRUCTOR.bio}
                    </p>
                    <ul className="mt-8 max-w-[520px] border-t">
                        {INSTRUCTOR.projects.map((project, index) => (
                            <li
                                key={index}
                                className="flex justify-between gap-4 border-b py-3.5 text-[15px]"
                            >
                                <span>{project.title}</span>
                                <span className="text-muted-foreground">
                                    {project.meta}
                                </span>
                            </li>
                        ))}
                    </ul>
                </div>
            </section>
        </>
    );
}
