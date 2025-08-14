<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\OrganizationRole;
use App\Models\SubscriptionRequest;
use App\Models\UserOrganizationRole;
use App\Policies\CompanyPolicy;
use App\Policies\OrganizationRolePolicy;
use App\Policies\SubscriptionRequestPolicy;
use App\Policies\UserOrganizationRolePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class => CompanyPolicy::class,
        SubscriptionRequest::class => SubscriptionRequestPolicy::class,
        OrganizationRole::class => OrganizationRolePolicy::class,
        UserOrganizationRole::class => UserOrganizationRolePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
