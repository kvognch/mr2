<?php

namespace App\Providers;

use App\Models\Contractor;
use App\Models\ContractorReview;
use App\Models\ServiceReview;
use App\Models\User;
use App\Policies\ContractorReviewPolicy;
use App\Policies\ContractorPolicy;
use App\Policies\ServiceReviewPolicy;
use App\Policies\UserPolicy;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Contractor::class => ContractorPolicy::class,
        ContractorReview::class => ContractorReviewPolicy::class,
        ServiceReview::class => ServiceReviewPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
