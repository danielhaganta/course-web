import { Head } from '@inertiajs/react';
import FootagePlaceholder from '@/components/footage-placeholder';

// Placeholder, ganti dengan data pengajar asli
const INSTRUCTOR = {
    name: '[Instructor Name]',
    photoSrc: null,
    bio: '[Short bio: background as a colorist, the kinds of projects worked on, and why this course exists.]',
    projects: [
        { title: '[Project title]', meta: '[Client · year]' },
        { title: '[Project title]', meta: '[Client · year]' },
        { title: '[Project title]', meta: '[Client · year]' },
    ],
};

export default function Instructor() {
    return (
        <>
            <Head title="Instructor">
                <meta
                    head-key="description"
                    name="description"
                    content="Meet the Poiema Academy instructor: their background as a colorist and the projects they have worked on."
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
                            alt={`Photo of ${INSTRUCTOR.name}`}
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
                                [Instructor photo]
                            </span>
                        </>
                    )}
                </div>

                <div className="flex flex-col justify-end px-6 py-12 lg:flex-1 lg:px-14 lg:pt-28 lg:pb-12">
                    <h1
                        id="instructor-title"
                        className="max-w-[10ch] font-display text-[clamp(56px,6vw,104px)] leading-[0.85] font-black [overflow-wrap:anywhere] uppercase"
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
