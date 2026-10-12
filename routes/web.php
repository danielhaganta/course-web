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
Route::inertia('instructor', 'instructor')->name('instructor');

// Isi placeholder, diganti teks dari pemilik website
Route::inertia('terms', 'info', [
    'title' => 'Terms & conditions',
    'placeholder' => '[Terms & conditions from the website owner: account usage rules, subscriptions, course purchases, cancellations, and refunds.]',
])->name('terms');
Route::inertia('privacy', 'info', [
    'title' => 'Privacy policy',
    'placeholder' => '[Privacy policy from the website owner: what data is collected, how it is used, who it is shared with, and how to delete an account.]',
])->name('privacy');
Route::inertia('contact', 'info', [
    'title' => 'Contact',
    'placeholder' => '[Contact details from the website owner: email, WhatsApp number, support hours, and address if any.]',
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
