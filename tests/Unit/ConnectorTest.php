<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests\Unit;

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

        config()->set('filesystems.azure_blob_storage.connections', [
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

        config()->set('filesystems.disks.disk_with_connection_config_array', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => [
                'account_name' => 'account_name',
                'account_key' => base64_encode('account_key'),
            ],
        ]);

        config()->set('filesystems.disks.disk_with_connection_string', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'AccountName=account_name;AccountKey=' . base64_encode('account_key'),
        ]);

        config()->set('filesystems.disks.disk_without_connection', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
        ]);

        config()->set('filesystems.disks.disk_with_named_connection', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'other',
        ]);

        config()->set('filesystems.disks.disk_with_invalid_connection', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'invalid_string_not_a_connection',
        ]);

        config()->set('filesystems.disks.disk_with_named_connection_string', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => 'connection_string',
        ]);
    }

    #[Test]
    public function it_uses_default_connection_when_connection_is_null(): void
    {
        self::assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_without_connection'));
    }

    #[Test]
    public function it_can_use_named_connection(): void
    {
        self::assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_named_connection'));
    }

    #[Test]
    public function it_throws_when_named_connection_not_configured(): void
    {
        $this->assertThrows(
            fn () => Storage::disk('disk_with_invalid_connection'),
            InvalidArgumentException::class,
            'Azure Blob Storage connection [invalid_string_not_a_connection] is not configured.',
        );
    }

    #[Test]
    public function it_can_be_created_from_driver_config_with_connection_string(): void
    {
        self::assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_connection_string'));
    }

    #[Test]
    public function it_can_be_created_from_driver_config_with_connection_array(): void
    {
        self::assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_connection_config_array'));
    }

    #[Test]
    public function it_can_connect_with_array_config(): void
    {
        $connector = new Connector;

        $filesystem = $connector
            ->connect([
                'account_name' => 'test_account',
                'account_key' => base64_encode('test_key'),
            ])
            ->container('test-container');

        self::assertSame(
            'https://test_account.blob.core.windows.net/test-container/file.txt',
            $filesystem->url('file.txt')
        );
    }

    #[Test]
    public function it_can_connect_with_connection_string(): void
    {
        $connector = new Connector;

        $filesystem = $connector
            ->connect('AccountName=test_account;AccountKey=' . base64_encode('test_key'))
            ->container('test-container');

        self::assertSame(
            'https://test_account.blob.core.windows.net/test-container/file.txt',
            $filesystem->url('file.txt')
        );
    }

    #[Test]
    public function it_can_connect_with_portal_sas_connection_string(): void
    {
        $connector = new Connector;

        $filesystem = $connector
            ->connect(
                'BlobEndpoint=https://test_account.blob.core.windows.net/;'
                . 'QueueEndpoint=https://test_account.queue.core.windows.net/;'
                . 'FileEndpoint=https://test_account.file.core.windows.net/;'
                . 'TableEndpoint=https://test_account.table.core.windows.net/;'
                . 'SharedAccessSignature=sv=2024-11-04&ss=b&srt=sco&sp=rwdlac&se=2027-01-01T00:00:00Z&sig=signature'
            )
            ->container('test-container');

        self::assertStringStartsWith(
            'https://test_account.blob.core.windows.net/test-container/file.txt',
            $filesystem->url('file.txt')
        );
    }

    #[Test]
    public function it_can_connect_with_development_storage_connection_string(): void
    {
        $connector = new Connector;

        $filesystem = $connector->connect('UseDevelopmentStorage=true')->container('test-container');

        self::assertSame(
            'http://127.0.0.1:10000/devstoreaccount1/test-container/file.txt',
            $filesystem->url('file.txt')
        );
    }

    #[Test]
    public function it_can_connect_with_named_connection(): void
    {
        $connector = new Connector([
            'my_connection' => [
                'account_name' => 'test_account',
                'account_key' => base64_encode('test_key'),
            ],
        ]);

        $filesystem = $connector->connect('my_connection')->container('test-container');

        self::assertSame(
            'https://test_account.blob.core.windows.net/test-container/file.txt',
            $filesystem->url('file.txt')
        );
    }

    #[Test]
    public function it_uses_default_connection_when_null_passed(): void
    {
        $connector = new Connector([
            'default' => [
                'account_name' => 'test_account',
                'account_key' => base64_encode('test_key'),
            ],
        ]);

        $filesystem = $connector->connect()->container('test-container');

        self::assertSame(
            'https://test_account.blob.core.windows.net/test-container/file.txt',
            $filesystem->url('file.txt')
        );
    }

    #[Test]
    public function it_throws_when_default_connection_missing(): void
    {
        $connector = new Connector;

        $this->assertThrows(
            fn () => $connector->connect(),
            InvalidArgumentException::class,
            'Azure Blob Storage connection [default] is not configured.',
        );
    }

    #[Test]
    public function it_can_use_connection_string_as_named_connection(): void
    {
        $connector = new Connector([
            'default' => 'AccountName=test_account;AccountKey=' . base64_encode('test_key'),
        ]);

        $filesystem = $connector->connect()->container('test-container');

        self::assertSame(
            'https://test_account.blob.core.windows.net/test-container/file.txt',
            $filesystem->url('file.txt')
        );
    }

    #[Test]
    public function it_can_use_disk_with_named_connection_string(): void
    {
        self::assertInstanceOf(FilesystemAdapter::class, Storage::disk('disk_with_named_connection_string'));
    }
}
