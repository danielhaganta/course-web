const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

// Contoh: 149000 -> "Rp 149.000"
export function formatRupiah(amount: number): string {
    return rupiah.format(amount);
}
