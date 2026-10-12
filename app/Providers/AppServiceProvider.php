<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Plan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureLargeUploads();

        // Disimpan di orders.purchasable_type, jangan diubah setelah ada data
        Relation::morphMap([
            'plan' => Plan::class,
            'course' => Course::class,
        ]);
    }

    /**
     * Upload file latihan lewat Livewire. Default Livewire: maks 12 MB dan 5 menit.
     */
    protected function configureLargeUploads(): void
    {
        $maxSizeKilobytes = Config::integer('filesystems.attachments.max_size_mb') * 1024;

        Config::set([
            'livewire.temporary_file_upload.rules' => ['required', 'file', "max:{$maxSizeKilobytes}"],
            'livewire.temporary_file_upload.max_upload_time' => 60,
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
