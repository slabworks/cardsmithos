<?php

namespace App\Providers;

use App\Models\BusinessStatistic;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
use App\Policies\BusinessStatisticPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ShipmentPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Shipment::class, ShipmentPolicy::class);
        Gate::policy(BusinessStatistic::class, BusinessStatisticPolicy::class);

        $this->configureDefaults();
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
