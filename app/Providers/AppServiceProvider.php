<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Paginasi bergaya Bootstrap 5 agar selaras dengan tema.
        Paginator::useBootstrapFive();

        // Lokalisasi tanggal ke Bahasa Indonesia (translatedFormat).
        Carbon::setLocale('id');
    }
}
