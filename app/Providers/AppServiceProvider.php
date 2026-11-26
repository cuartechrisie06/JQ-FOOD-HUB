<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Observers\OrderObserver;
use App\Observers\PaymentObserver;
use App\Policies\OrderPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
        Paginator::useBootstrapFive();
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
        Order::observe(OrderObserver::class);
        Payment::observe(PaymentObserver::class);
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
