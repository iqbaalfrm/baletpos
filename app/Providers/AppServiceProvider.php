<?php

namespace App\Providers;

use App\Models\OperationalCost;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Observers\PurchaseDetailObserver;
use App\Observers\TransactionDetailObserver;
use App\Observers\TransactionObserver;
use App\Policies\OperationalCostPolicy;
use App\Policies\ProductPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
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
        Transaction::observe(TransactionObserver::class);
        TransactionDetail::observe(TransactionDetailObserver::class);
        \App\Models\PurchaseDetail::observe(PurchaseDetailObserver::class);

        // Register policies
        \Gate::policy(User::class, UserPolicy::class);
        \Gate::policy(Product::class, ProductPolicy::class);
        \Gate::policy(Transaction::class, TransactionPolicy::class);
        \Gate::policy(OperationalCost::class, OperationalCostPolicy::class);
    }
}
