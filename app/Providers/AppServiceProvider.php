<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Observers\TransactionObserver;
use App\Observers\TransactionDetailObserver;
use App\Models\PurchaseDetail;
use App\Observers\PurchaseDetailObserver;

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
    PurchaseDetail::observe(PurchaseDetailObserver::class);
}
}
