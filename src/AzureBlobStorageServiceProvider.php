<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AzureBlobStorageServiceProvider extends ServiceProvider
{
    public function boot(Connector $connector): void
    {
        Storage::extend('azure_blob_storage', function (Application $app, array $config) use ($connector) {
            return $connector
                ->connect($config['connection'] ?? null)
                ->container($config['container'], $config['prefix'] ?? '', $config);
        });
    }

    public function register(): void
    {
        $this->app->singleton(Connector::class, function (Application $app): Connector {
            /** @var array<string, string|array<string, string>> $connections */
            $connections = (array) $app['config']->get('filesystems.azure_blob_storage.connections', []);

            return new Connector($connections);
        });
    }

    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return [
            Connector::class,
        ];
    }
}
