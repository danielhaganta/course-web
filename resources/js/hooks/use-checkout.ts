import { router, useHttp } from '@inertiajs/react';
import { useState } from 'react';
import type { CheckoutResponse } from '@/types';

type SnapCallbacks = {
    onSuccess: () => void;
    onPending: () => void;
    onError: () => void;
    onClose: () => void;
};

type Snap = {
    pay: (token: string, callbacks: SnapCallbacks) => void;
};

declare global {
    interface Window {
        snap?: Snap;
    }
}

const DEFAULT_ERROR =
    'Pembayaran belum bisa disiapkan. Coba lagi beberapa saat.';

/**
 * Minta snap token ke server, lalu buka popup Midtrans Snap.
 * Apa pun hasil popup, user diarahkan ke halaman finish. Status dicek di server.
 */
export function useCheckout() {
    const http = useHttp<Record<string, never>, CheckoutResponse>({});
    const [isPreparing, setPreparing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    async function startCheckout(url: string): Promise<void> {
        setError(null);
        setPreparing(true);

        try {
            const response = await http.post(url, {
                onError: (errors) =>
                    setError(Object.values(errors)[0] ?? DEFAULT_ERROR),
            });

            // 422: pesan sudah diset lewat onError
            if (!response) {
                return;
            }

            await openSnap(response);
        } catch (exception) {
            setError(errorMessage(exception));
        } finally {
            setPreparing(false);
        }
    }

    /**
     * Buka ulang popup untuk order pending dengan snap token yang sudah ada.
     */
    async function continuePayment(payment: CheckoutResponse): Promise<void> {
        setError(null);
        setPreparing(true);

        try {
            await openSnap(payment);
        } catch {
            setError('Popup pembayaran gagal dibuka. Coba lagi beberapa saat.');
        } finally {
            setPreparing(false);
        }
    }

    return { startCheckout, continuePayment, isPreparing, error };
}

async function openSnap(payment: CheckoutResponse): Promise<void> {
    const snap = await loadSnap(payment.snap_js_url, payment.client_key);
    const goToFinish = () => router.visit(payment.finish_url);

    snap.pay(payment.snap_token, {
        onSuccess: goToFinish,
        onPending: goToFinish,
        onError: goToFinish,
        onClose: goToFinish,
    });
}

function loadSnap(src: string, clientKey: string): Promise<Snap> {
    if (window.snap) {
        return Promise.resolve(window.snap);
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.dataset.clientKey = clientKey;
        script.onload = () =>
            window.snap
                ? resolve(window.snap)
                : reject(new Error('Snap tidak tersedia'));
        script.onerror = () => {
            script.remove();
            reject(new Error('Gagal memuat Snap'));
        };
        document.head.appendChild(script);
    });
}

// Respons error server berisi JSON { message }
function errorMessage(exception: unknown): string {
    if (
        exception instanceof Error &&
        'response' in exception &&
        typeof exception.response === 'object' &&
        exception.response !== null &&
        'data' in exception.response &&
        typeof exception.response.data === 'string'
    ) {
        try {
            const body: unknown = JSON.parse(exception.response.data);

            if (
                typeof body === 'object' &&
                body !== null &&
                'message' in body &&
                typeof body.message === 'string'
            ) {
                return body.message;
            }
        } catch {
            return DEFAULT_ERROR;
        }
    }

    return DEFAULT_ERROR;
}
