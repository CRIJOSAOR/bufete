<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Solo cuando se entra por el túnel de Cloudflare
        if (str_ends_with(request()->getHost(), 'trycloudflare.com')) {
            URL::forceScheme('https');
        }
    }
}