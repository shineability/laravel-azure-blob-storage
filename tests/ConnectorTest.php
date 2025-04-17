<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

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

    public function test_it_can_not_be_created_from_driver_config_with_non_existing_connection()
    {
        $this->expectException(InvalidArgumentException::class);

        Storage::disk('disk_with_invalid_connection_config_string');
    }

    public function test_it_can_be_created_from_driver_config_with_fallback_to_default_connection()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_fallback_to_default_connection'));
    }

    public function test_it_can_be_created_from_driver_config_with_connection_string()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_connection_config_string'));
    }

    public function test_it_can_be_created_from_driver_config_with_connection_array()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_connection_config_array'));
    }
}
