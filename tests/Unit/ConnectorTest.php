<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests\Unit;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Shineability\LaravelAzureBlobStorage\Connector;
use Shineability\LaravelAzureBlobStorage\ContainerFilesystemFactory;
use Shineability\LaravelAzureBlobStorage\Tests\TestCase;

class ConnectorTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('filesystems.azure_blob_storage.connections', [
            'default' => [
                'account_name' => 'default_account',
                'account_key' => base64_encode('default_key'),
            ],
            'other' => [
                'account_name' => 'other_account',
                'account_key' => base64_encode('other_key'),
            ],
            'connection_string' => 'AccountName=connection_string_account;AccountKey=' . base64_encode('connection_string_key'),
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

        $app['config']->set('filesystems.disks.disk_with_connection_string', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'AccountName=account_name;AccountKey=' . base64_encode('account_key'),
        ]);

        $app['config']->set('filesystems.disks.disk_without_connection', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
        ]);

        $app['config']->set('filesystems.disks.disk_with_named_connection', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'other',
        ]);

        $app['config']->set('filesystems.disks.disk_with_invalid_connection', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'invalid_string_not_a_connection',
        ]);

        $app['config']->set('filesystems.disks.disk_with_named_connection_string', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'connection_string',
        ]);
    }

    #[Test]
    public function it_uses_default_connection_when_connection_is_null()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_without_connection'));
    }

    #[Test]
    public function it_can_use_named_connection()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_named_connection'));
    }

    #[Test]
    public function it_throws_when_named_connection_not_configured()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Azure Blob Storage connection [invalid_string_not_a_connection] is not configured.');

        Storage::disk('disk_with_invalid_connection');
    }

    #[Test]
    public function it_can_be_created_from_driver_config_with_connection_string()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_connection_string'));
    }

    #[Test]
    public function it_can_be_created_from_driver_config_with_connection_array()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_connection_config_array'));
    }

    #[Test]
    public function it_can_connect_with_array_config()
    {
        $connector = new Connector;

        $factory = $connector->connect([
            'account_name' => 'test_account',
            'account_key' => base64_encode('test_key'),
        ]);

        $this->assertInstanceOf(ContainerFilesystemFactory::class, $factory);
    }

    #[Test]
    public function it_can_connect_with_connection_string()
    {
        $connector = new Connector;

        $factory = $connector->connect('AccountName=test_account;AccountKey=' . base64_encode('test_key'));

        $this->assertInstanceOf(ContainerFilesystemFactory::class, $factory);
    }

    #[Test]
    public function it_can_connect_with_named_connection()
    {
        $connector = new Connector([
            'default' => [
                'account_name' => 'test_account',
                'account_key' => base64_encode('test_key'),
            ],
        ]);

        $factory = $connector->connect('default');

        $this->assertInstanceOf(ContainerFilesystemFactory::class, $factory);
    }

    #[Test]
    public function it_uses_default_connection_when_null_passed()
    {
        $connector = new Connector([
            'default' => [
                'account_name' => 'test_account',
                'account_key' => base64_encode('test_key'),
            ],
        ]);

        $factory = $connector->connect();

        $this->assertInstanceOf(ContainerFilesystemFactory::class, $factory);
    }

    #[Test]
    public function it_throws_when_default_connection_missing()
    {
        $connector = new Connector;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Azure Blob Storage connection [default] is not configured.');

        $connector->connect();
    }

    #[Test]
    public function it_can_use_connection_string_as_named_connection()
    {
        $connector = new Connector([
            'default' => 'AccountName=test_account;AccountKey=' . base64_encode('test_key'),
        ]);

        $factory = $connector->connect();

        $this->assertInstanceOf(ContainerFilesystemFactory::class, $factory);
    }

    #[Test]
    public function it_can_use_disk_with_named_connection_string()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_named_connection_string'));
    }
}
