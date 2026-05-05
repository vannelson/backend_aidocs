<?php

namespace App\Providers;

use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\DocumentShareRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\DocumentRepository;
use App\Repositories\DocumentShareRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\DocumentServiceInterface;
use App\Services\Contracts\ShareServiceInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Services\DocumentService;
use App\Services\ShareService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DocumentServiceInterface::class, DocumentService::class);
        $this->app->bind(ShareServiceInterface::class, ShareService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DocumentRepositoryInterface::class, DocumentRepository::class);
        $this->app->bind(DocumentShareRepositoryInterface::class, DocumentShareRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
