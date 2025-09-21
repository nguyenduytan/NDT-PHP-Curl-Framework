<?php
declare(strict_types=1);
namespace ndtan\Curl\Integrations\Laravel;
use Illuminate\Support\ServiceProvider;
use ndtan\Curl\Http\Http;

final class NdtCurlServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('ndt.http', fn()=> Http::class);
        $this->mergeConfigFrom(__DIR__.'/../../../config/ndt_curl.php', 'ndt_curl');
    }
    public function boot(): void
    {
        $this->publishes([__DIR__.'/../../../config/ndt_curl.php' => config_path('ndt_curl.php')], 'config');
    }
}
