<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests\Unit;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Shineability\LaravelAzureBlobStorage\Connector;
use Shineability\LaravelAzureBlobStorage\Tests\TestCase;

class ConnectorTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('filesystems.azure_blob_storage.connections', [
            'default' => [
                'account_name' => 'default_account_name',
                'account_key' => base64_encode('default_account_key'),
            ],
            'custom' => [
                'account_name' => 'custom_account_name',
                'account_key' => base64_encode('custom_account_key'),
            ],
        ]);

        $app['config']->set('filesystems.disks.disk_with_connection_config_array', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => [
                'account_name' => 'account_name',
                'account_key' => base64_encode('account_key'),
            ],
        ]);

        $app['config']->set('filesystems.disks.disk_with_connection_config_string', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'custom',
        ]);

        $app['config']->set('filesystems.disks.disk_with_fallback_to_default_connection', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
        ]);

        $app['config']->set('filesystems.disks.disk_with_invalid_connection_config_string', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'INVALID_CONNECTION',
        ]);
    }

    #[Test]
    public function it_can_not_be_created_from_driver_config_with_non_existing_connection()
    {
        $this->expectException(InvalidArgumentException::class);

        Storage::disk('disk_with_invalid_connection_config_string');
    }

    #[Test]
    public function it_can_be_created_from_driver_config_with_fallback_to_default_connection()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_fallback_to_default_connection'));
    }

    #[Test]
    public function it_can_be_created_from_driver_config_with_connection_string()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_connection_config_string'));
    }

    #[Test]
    public function it_can_be_created_from_driver_config_with_connection_array()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_connection_config_array'));
    }

    #[Test]
    public function it_can_add_a_connection_at_runtime()
    {
        $this->app['config']->set('filesystems.disks.disk_with_added_connection', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'connection' => 'added',
        ]);

        $connector = $this->app->make(Connector::class);

        $connector->addConnection('added', [
            'account_name' => 'account_name',
            'account_key' => base64_encode('account_key'),
        ]);

        $this->assertInstanceOf(Filesystem::class, $connector->connect('added')->container('container'));
        $this->assertInstanceOf(Filesystem::class, Storage::disk('disk_with_added_connection'));
    }
}
