<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Shineability\LaravelAzureBlobStorage\Tests\TestCase;

class StorageDriverTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config()->set('filesystems.disks.azure_blob_storage_disk', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => [
                'account_name' => 'account_name',
                'account_key' => base64_encode('account_key'),
            ],
        ]);
    }

    #[Test]
    public function it_can_generate_a_public_url(): void
    {
        $publicUrl = 'https://account_name.blob.core.windows.net/container/prefix/foobar.txt';

        self::assertSame($publicUrl, Storage::disk('azure_blob_storage_disk')->url('foobar.txt'));
    }

    #[Test]
    public function it_can_generate_a_temporary_url(): void
    {
        $expirationDate = now()->addHour();

        $temporaryUrl = Storage::disk('azure_blob_storage_disk')->temporaryUrl('foobar.txt', $expirationDate);

        self::assertStringContainsString('https://account_name.blob.core.windows.net/container/prefix/foobar.txt', $temporaryUrl);

        self::assertMatchesRegularExpression('/sig=(.*)/', $temporaryUrl);
        self::assertMatchesRegularExpression('/sp=r/', $temporaryUrl);

        self::assertMatchesRegularExpression(
            sprintf('/se=%sT%sZ/', $expirationDate->format('Y-m-d'), $expirationDate->format('H:i:s')),
            $temporaryUrl
        );
    }

    #[Test]
    public function it_can_generate_a_temporary_upload_url(): void
    {
        $expirationDate = now()->addHour();

        $temporaryUpload = Storage::disk('azure_blob_storage_disk')->temporaryUploadUrl('foobar.txt', $expirationDate);

        self::assertArrayHasKey('url', $temporaryUpload);
        self::assertArrayHasKey('headers', $temporaryUpload);

        self::assertStringContainsString('https://account_name.blob.core.windows.net/container/prefix/foobar.txt', $temporaryUpload['url']);

        self::assertMatchesRegularExpression('/sig=(.*)/', $temporaryUpload['url']);
        self::assertMatchesRegularExpression('/sp=w/', $temporaryUpload['url']);

        self::assertMatchesRegularExpression(
            sprintf('/se=%sT%sZ/', $expirationDate->format('Y-m-d'), $expirationDate->format('H:i:s')),
            $temporaryUpload['url']
        );

        self::assertArraySubset(['x-ms-blob-type' => 'BlockBlob'], $temporaryUpload['headers']);
    }
}
