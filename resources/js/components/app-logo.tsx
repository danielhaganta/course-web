import { usePage } from '@inertiajs/react';
import { useInitials } from '@/hooks/use-initials';

export default function AppLogo() {
    const { name } = usePage().props;
    const getInitials = useInitials();

    return (
        <>
            {/* Inisial tetap terlihat saat sidebar diciutkan */}
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-xs font-semibold text-sidebar-primary-foreground">
                {getInitials(name)}
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    {name}
                </span>
            </div>
        </>
    );
}
