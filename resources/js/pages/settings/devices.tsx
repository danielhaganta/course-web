import { Form, Head } from '@inertiajs/react';
import { Monitor, Smartphone } from 'lucide-react';
import DeviceController from '@/actions/App/Http/Controllers/Settings/DeviceController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { edit } from '@/routes/devices';

type DeviceSession = {
    key: string;
    browser: string;
    platform: string;
    ip_address: string | null;
    last_active: string;
    is_current: boolean;
};

type Props = {
    sessions: DeviceSession[];
    // Null untuk admin: tidak dibatasi
    maxDevices: number | null;
};

export default function Devices({ sessions, maxDevices }: Props) {
    const hasOtherDevices = sessions.some((session) => !session.is_current);

    return (
        <>
            <Head title="Perangkat aktif" />

            <h1 className="sr-only">Perangkat aktif</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Perangkat aktif"
                    description={
                        maxDevices === null
                            ? 'Akun admin tidak dibatasi jumlah perangkat.'
                            : `Akun ini bisa dipakai di maksimal ${maxDevices} perangkat sekaligus.`
                    }
                />

                <ul className="divide-y rounded-lg border">
                    {sessions.map((session) => (
                        <DeviceRow key={session.key} session={session} />
                    ))}
                </ul>

                {hasOtherDevices && (
                    <Form
                        {...DeviceController.destroyOthers.form()}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <Button variant="destructive" disabled={processing}>
                                Keluarkan semua perangkat lain
                            </Button>
                        )}
                    </Form>
                )}
            </div>
        </>
    );
}

function DeviceRow({ session }: { session: DeviceSession }) {
    const isMobile = ['Android', 'iOS'].includes(session.platform);
    const Icon = isMobile ? Smartphone : Monitor;

    return (
        <li className="flex flex-wrap items-center gap-3 p-4 text-sm">
            <Icon
                className="size-5 shrink-0 text-muted-foreground"
                aria-hidden
            />
            <div className="min-w-0 flex-1">
                <p className="font-medium">
                    {session.browser} di {session.platform}
                </p>
                <p className="text-muted-foreground">
                    {session.ip_address ?? 'IP tidak diketahui'} · Aktif{' '}
                    {session.last_active}
                </p>
            </div>
            {session.is_current ? (
                <Badge variant="secondary">Perangkat ini</Badge>
            ) : (
                <Form
                    {...DeviceController.destroy.form(session.key)}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={processing}
                        >
                            Keluarkan
                        </Button>
                    )}
                </Form>
            )}
        </li>
    );
}

Devices.layout = {
    breadcrumbs: [
        {
            title: 'Perangkat aktif',
            href: edit(),
        },
    ],
};
