<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Repositories\UserRepository;
use Modules\Auth\Repositories\UserRepositoryInterface;


class UserProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void {
            $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}
