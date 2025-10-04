<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests\Integration;

use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\Blob\Models\CreateContainerOptions;
use AzureOss\Storage\Blob\Models\PublicAccessType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Shineability\LaravelAzureBlobStorage\Tests\TestCase;

class BlobStorageTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('filesystems.disks.azure_blob_storage_disk', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            // 'prefix' => 'prefix',
            'connection' => [
                'default_endpoints_protocol' => 'http',
                'blob_endpoint' => 'http://127.0.0.1:10000/devstoreaccount1',
                'account_name' => 'devstoreaccount1',
                'account_key' => 'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==',
            ],
        ]);
    }

    /**
     * @throws \AzureOss\Storage\Blob\Exceptions\InvalidConnectionStringException
     */
    // private static function createContainerClient(): BlobContainerClient
    // {
    //     return BlobServiceClient::fromConnectionString('UseDevelopmentStorage=true')->getContainerClient('container');
    // }

    private static function createContainerClient(): BlobContainerClient
    {
        $connectionString = 'UseDevelopmentStorage=true';

        return BlobServiceClient::fromConnectionString($connectionString)->getContainerClient('container');
    }

    /**
     * @throws \AzureOss\Storage\Blob\Exceptions\InvalidConnectionStringException
     */
    public static function setUpBeforeClass(): void
    {
        self::createContainerClient()->deleteIfExists();
        self::createContainerClient()->create(new CreateContainerOptions(PublicAccessType::CONTAINER));
        // self::createContainerClient()->create();
    }

    #[Test]
    public function it_works_using_the_azurite_emulator(): void
    {
        $disk = Storage::disk('azure_blob_storage_disk');

        $disk->deleteDirectory('');

        self::assertFalse($disk->exists('non_existing_file.txt'));

        $disk->put('file.txt', 'content');

        self::assertTrue($disk->exists('file.txt'));

        self::assertEquals('content', $disk->get('file.txt'));
        self::assertEquals('content', Http::get($disk->temporaryUrl('file.txt', now()->addMinute()))->body());
        self::assertEquals('content', Http::get($disk->url('file.txt'))->body());

        $disk->copy('file.txt', 'file_copy.txt');
        $disk->copy('file.txt', 'aaa.txt');
        $disk->copy('aaa.txt', 'bbb.txt');

        // dd(get_class($disk));

        self::assertTrue($disk->exists('file_copy.txt'));

        // $disk->move('file_copy.txt', 'file_move.txt');
        //
        // self::assertFalse($disk->exists('file_copy.txt'));
        // self::assertTrue($disk->exists('file_move.txt'));
        //
        // self::assertCount(2, $disk->allFiles());
        //
        // $disk->deleteDirectory('');
        //
        // self::assertCount(0, $disk->allFiles());
    }
}
