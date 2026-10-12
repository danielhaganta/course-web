<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LessonProgressController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PricingController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'home')->name('home');
Route::inertia('before-after', 'before-after')->name('before-after');
Route::inertia('pengajar', 'instructor')->name('instructor');

// Isi placeholder, diganti teks dari pemilik website
Route::inertia('syarat-ketentuan', 'info', [
    'title' => 'Syarat & ketentuan',
    'placeholder' => '[Isi syarat & ketentuan dari pemilik website: aturan penggunaan akun, langganan, pembelian course, pembatalan, dan pengembalian dana.]',
])->name('terms');
Route::inertia('kebijakan-privasi', 'info', [
    'title' => 'Kebijakan privasi',
    'placeholder' => '[Isi kebijakan privasi dari pemilik website: data apa yang dikumpulkan, untuk apa dipakai, dengan siapa dibagikan, dan cara menghapus akun.]',
])->name('privacy');
Route::inertia('kontak', 'info', [
    'title' => 'Kontak',
    'placeholder' => '[Isi kontak dari pemilik website: email, nomor WhatsApp, jam layanan, dan alamat bila ada.]',
])->name('contact');

Route::get('pricing', [PricingController::class, 'index'])->name('pricing');

Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('courses/{course:slug}/lessons/{lesson}', [LessonController::class, 'show'])
    ->scopeBindings()
    ->name('lessons.show');
Route::post('courses/{course:slug}/lessons/{lesson}/progress', [LessonProgressController::class, 'update'])
    ->middleware('auth')
    ->scopeBindings()
    ->name('lessons.progress');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');

    Route::post('checkout/plans/{plan:slug}', [CheckoutController::class, 'plan'])->name('checkout.plan');
    Route::post('checkout/courses/{course:slug}', [CheckoutController::class, 'course'])->name('checkout.course');
    Route::get('checkout/{order:code}/finish', [CheckoutController::class, 'finish'])->name('checkout.finish');
    Route::post('checkout/{order:code}/status', [CheckoutController::class, 'refreshStatus'])
        ->middleware('throttle:10,1')
        ->name('checkout.status');
});

// Dikecualikan dari CSRF di bootstrap/app.php, keaslian dicek lewat signature_key
Route::post('webhooks/midtrans', MidtransWebhookController::class)->name('webhooks.midtrans');

require __DIR__.'/settings.php';
