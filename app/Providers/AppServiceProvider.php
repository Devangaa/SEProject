<?php

namespace App\Providers;

use App\Models\Keranjang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        if (str_contains(config('app.url'), 'ngrok-free.app') || config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // Share cart count to navbar
        View::composer('components.navbar', function ($view) {
            $cartCount = 0;
            if (auth()->check() && auth()->user()->role === 'pelanggan') {
                $cartCount = Keranjang::where('user_id', auth()->id())->count();
            }
            $view->with('cartCount', $cartCount);
        });
    }
}
