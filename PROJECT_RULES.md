# Aturan Project: Website Course Membership

File ini berisi aturan kerja untuk AI agent di project ini. Ikuti semua aturan di bawah setiap kali menulis atau mengubah kode.

## Konteks project

Website course video dengan sistem membership. Member membuat akun, membeli paket (langganan atau lifetime), lalu bisa menonton course sesuai paketnya. Video diputar via streaming dan tidak boleh didownload.

Stack:
- Laravel 13, PHP 8.3
- Inertia + React + TypeScript, Tailwind CSS, shadcn/ui (React starter kit)
- MySQL 8
- Pest untuk testing
- Filament untuk admin panel
- Bunny Stream (video), Cloudflare R2 (file download), Midtrans (pembayaran)

## Cara kerja

1. **Kerjakan hanya yang diminta.** Jangan menambah fitur, file, atau refactor yang tidak diminta. Jangan menghapus atau mengubah kode yang tidak berhubungan dengan tugas.
2. **Kalau instruksi tidak jelas atau ada beberapa cara yang sama-sama masuk akal, tanya dulu** sebelum menulis kode.
3. **Jangan install package baru tanpa izin.** Jelaskan dulu kenapa package itu dibutuhkan dan apa alternatifnya.
4. **Ikuti pola yang sudah ada di project.** Sebelum membuat file baru, lihat bagaimana file sejenis ditulis di project ini dan ikuti gayanya.
5. **Perubahan kecil dan fokus.** Satu tugas, satu tujuan. Kalau tugasnya besar, pecah jadi beberapa langkah dan konfirmasi rencananya dulu.
6. **Setelah selesai, beri ringkasan singkat:** file apa saja yang diubah atau dibuat, dan perintah yang perlu dijalankan (misalnya `php artisan migrate`).
7. Balas dalam bahasa Indonesia.

## Gaya kode

- **Pakai cara paling modern** yang didukung versi Laravel, PHP, React, dan TypeScript di project ini. Jangan pakai pola lama atau yang sudah deprecated.
- **Rapi, bersih, dan mudah dibaca.** Utamakan kode yang langsung dipahami daripada kode yang terlihat pintar.
- **Jangan overengineer.** Tidak perlu interface, abstraksi, pattern, atau konfigurasi tambahan kalau belum dibutuhkan. Tulis solusi paling sederhana yang benar.
- Fungsi dan method pendek, satu tanggung jawab.
- Nama variabel, fungsi, class, dan file dalam bahasa Inggris, jelas, dan deskriptif. Hindari singkatan yang tidak umum.
- Hindari kode duplikat, tapi jangan membuat abstraksi hanya untuk dua baris yang mirip.
- Hapus kode yang tidak terpakai, import yang tidak dipakai, dan `console.log` / `dd()` sebelum selesai.

## Komentar

- **Tulis komentar hanya untuk hal penting**: alasan di balik keputusan, aturan bisnis, atau bagian yang tidak jelas dari kodenya sendiri.
- **Jangan berkomentar untuk hal yang sudah jelas** dari kodenya (misalnya `// simpan user` di atas `$user->save()`).
- **Bahasa komentar to the point**, seperti catatan developer. Tidak bertele-tele, tidak memakai gaya bahasa AI, tidak ada basa-basi seperti "Fungsi ini bertugas untuk...", "Mari kita...", atau "Berikut adalah...".
- Komentar dalam bahasa Indonesia singkat.

Contoh:

```php
// Buruk
// Fungsi ini digunakan untuk memeriksa apakah user memiliki akses ke course yang diberikan

// Baik
// Lifetime: ends_at null
```

## Laravel

- Validasi input pakai **Form Request**, bukan langsung di controller.
- Controller tipis. Logika bisnis yang dipakai di beberapa tempat taruh di class terpisah (misalnya `app/Services` atau `app/Actions`), tapi jangan buat class baru kalau logikanya cuma dipakai sekali dan pendek.
- Semua aturan akses (boleh menonton atau tidak) ada di satu tempat. Jangan cek akses dengan cara berbeda-beda di tiap controller.
- Pakai Eloquent dan relasi. Hindari N+1 query dengan eager loading (`with()`).
- Pakai enum PHP untuk status dan tipe (misalnya status order).
- Set `$fillable` di setiap model. Jangan pakai `$guarded = []`.
- **Jangan mengubah migration yang sudah dijalankan atau sudah di-push.** Buat migration baru untuk perubahan tabel.
- Nominal uang disimpan sebagai integer rupiah, bukan float.
- Timezone `Asia/Jakarta`.
- Akses hanya diaktifkan dari webhook pembayaran yang signature-nya valid, tidak dari callback frontend.

## React & TypeScript

- Function component dan hooks. Tidak ada class component.
- TypeScript ketat. **Jangan pakai `any`.** Definisikan tipe untuk props dan data dari server.
- Pakai komponen shadcn/ui yang sudah ada sebelum membuat komponen baru.
- Styling pakai Tailwind. Tidak ada file CSS terpisah per komponen.
- Ikuti cara starter kit untuk route dan form (Inertia `router`, `useForm`, dan route helper yang sudah dipakai di project).
- Komponen kecil dan fokus. Pecah kalau satu file sudah terlalu panjang atau mengerjakan banyak hal.
- Teks yang tampil ke user dalam bahasa Indonesia, singkat, dan jelas.
- Perhatikan dasar aksesibilitas: label pada input, `alt` pada gambar, tombol pakai `<button>`.
- Tampilan harus responsif dan nyaman di HP.

## Keamanan

- Jangan pernah menulis API key, password, atau rahasia lain langsung di kode. Semua lewat `.env` dan `config/`.
- Jangan membaca, menampilkan, atau mengubah isi `.env` kecuali diminta.
- Selalu validasi input dan cek otorisasi di backend, walaupun frontend sudah membatasi.
- Link video dan file download harus berbatas waktu dan hanya dibuat untuk user yang berhak.

## Testing & pengecekan

- Logika penting (akses per paket, aktivasi langganan setelah bayar, perpanjangan, webhook) harus punya test Pest.
- Sebelum menyatakan selesai, jalankan pengecekan yang relevan: `php artisan test`, `vendor/bin/pint` untuk format PHP, dan `npm run lint` / `npm run types` untuk frontend (sesuai script yang ada di `package.json`).
- Kalau ada test atau pengecekan yang gagal, perbaiki atau laporkan. Jangan diabaikan.

## Jangan disentuh

- `vendor/`, `node_modules/`, `public/build/`
- `.env`
- File migration yang sudah dijalankan
- Konfigurasi dan file yang tidak berhubungan dengan tugas

## Git

- Pesan commit singkat dengan format: `feat: ...`, `fix: ...`, `refactor: ...`, `chore: ...`, `docs: ...`, `test: ...`
- Jangan commit atau push kecuali diminta.
