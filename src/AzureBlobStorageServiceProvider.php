<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AzureBlobStorageServiceProvider extends ServiceProvider
{
    public function boot(Connector $manager): void
    {
        Storage::extend('azure_blob_storage', function (Application $app, array $config) use ($manager) {

            return $manager
                ->connect($config['connection'] ?? null)
                ->container($config['container'], $config['prefix'] ?? '', $config);
        });
    }

    public function register(): void
    {
        $this->app->singleton(Connector::class, function (Application $app) {
            return new Connector((array) $app['config']->get('filesystems.azure_blob_storage.connections', []));
        });
    }

    public function provides(): array
    {
        return [
            Connector::class,
        ];
    }
}
