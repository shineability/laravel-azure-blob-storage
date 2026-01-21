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
    private const AZURITE_PORT = 10000;

    private const CONTAINER_NAME = 'container';

    private static function azuriteHost(): string
    {
        $host = getenv('AZURITE_HOST');

        return is_string($host) ? $host : '127.0.0.1';
    }

    /**
     * @return array{DefaultEndpointsProtocol: string, BlobEndpoint: string, AccountName: string, AccountKey: string}
     */
    private static function developmentConnection(): array
    {
        return [
            'DefaultEndpointsProtocol' => 'http',
            'BlobEndpoint' => sprintf('http://%s:%d/devstoreaccount1', self::azuriteHost(), self::AZURITE_PORT),
            'AccountName' => 'devstoreaccount1',
            'AccountKey' => 'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==',
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config()->set('filesystems.disks.azure_blob_storage_disk', [
            'driver' => 'azure_blob_storage',
            'container' => self::CONTAINER_NAME,
            'prefix' => 'prefix',
            'connection' => Connection::fromArray(self::developmentConnection())->toString(),
        ]);
    }

    #[Test]
    public function it_works_using_the_azurite_emulator(): void
    {
        if (!self::isAzuriteRunning()) {
            self::markTestSkipped(sprintf('Azurite emulator is not running on %s:%d', self::azuriteHost(), self::AZURITE_PORT));
        }

        $this->createContainer();

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

    private function createContainer(): void
    {
        $connectionString = Connection::fromArray(self::developmentConnection())->toString();

        $containerClient = BlobServiceClient::fromConnectionString($connectionString)->getContainerClient(self::CONTAINER_NAME);
        $containerClient->deleteIfExists();
        $containerClient->create(new CreateContainerOptions(PublicAccessType::CONTAINER));
    }

    private static function isAzuriteRunning(): bool
    {
        $connection = @fsockopen(self::azuriteHost(), self::AZURITE_PORT, $errno, $errstr, 1);

        if ($connection === false) {
            return false;
        }

        fclose($connection);

        return true;
    }
}
