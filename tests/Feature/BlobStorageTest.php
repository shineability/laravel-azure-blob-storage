<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests\Feature;

use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\Blob\Models\CreateContainerOptions;
use AzureOss\Storage\Blob\Models\PublicAccessType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Shineability\LaravelAzureBlobStorage\Connection;
use Shineability\LaravelAzureBlobStorage\Tests\TestCase;

class BlobStorageTest extends TestCase
{
    private const AZURITE_HOST = '127.0.0.1';

    private const AZURITE_PORT = 10000;

    private const CONTAINER_NAME = 'container';

    private const DEVELOPMENT_CONNECTION = [
        'DefaultEndpointsProtocol' => 'http',
        'BlobEndpoint' => 'http://127.0.0.1:10000/devstoreaccount1',
        'AccountName' => 'devstoreaccount1',
        'AccountKey' => 'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==',
    ];

    /**
     * @throws \AzureOss\Storage\Blob\Exceptions\InvalidConnectionStringException
     */
    public static function setUpBeforeClass(): void
    {
        if (!self::isAzuriteRunning()) {
            self::markTestSkipped('Azurite emulator is not running on port 10000');
        }

        $connectionString = Connection::fromArray(self::DEVELOPMENT_CONNECTION)->toString();

        $containerClient = BlobServiceClient::fromConnectionString($connectionString)->getContainerClient(self::CONTAINER_NAME);
        $containerClient->deleteIfExists();
        $containerClient->create(new CreateContainerOptions(PublicAccessType::CONTAINER));
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config()->set('filesystems.disks.azure_blob_storage_disk', [
            'driver' => 'azure_blob_storage',
            'container' => self::CONTAINER_NAME,
            'prefix' => 'prefix',
            'connection' => Connection::fromArray(self::DEVELOPMENT_CONNECTION)->toString(),
        ]);
    }

    #[Test]
    public function it_works_using_the_azurite_emulator(): void
    {
        $disk = Storage::disk('azure_blob_storage_disk');

        $disk->deleteDirectory('');

        self::assertFalse($disk->exists('non_existing_file.txt'));

        $disk->put('file.txt', 'content');

        self::assertTrue($disk->exists('file.txt'));
        self::assertSame('content', $disk->get('file.txt'));
        self::assertSame('content', Http::get($disk->temporaryUrl('file.txt', now()->addMinute()))->body());
        self::assertSame('content', Http::get($disk->url('file.txt'))->body());

        $disk->copy('file.txt', 'file_copy.txt');

        self::assertTrue($disk->exists('file_copy.txt'));

        $disk->move('file_copy.txt', 'file_move.txt');

        self::assertFalse($disk->exists('file_copy.txt'));
        self::assertTrue($disk->exists('file_move.txt'));
        self::assertCount(2, $disk->allFiles());

        $disk->deleteDirectory('');

        self::assertCount(0, $disk->allFiles());
    }

    private static function isAzuriteRunning(): bool
    {
        $connection = @fsockopen(self::AZURITE_HOST, self::AZURITE_PORT, $errno, $errstr, 1);

        if ($connection === false) {
            return false;
        }

        fclose($connection);

        return true;
    }
}
