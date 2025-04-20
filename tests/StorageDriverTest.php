<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests;

use Illuminate\Support\Facades\Storage;

class StorageDriverTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('filesystems.disks.azure_blob_storage_disk', [
            'driver' => 'azure_blob_storage',
            'container' => 'container',
            'prefix' => 'prefix',
            'connection' => [
                'account_name' => 'account_name',
                'account_key' => base64_encode('account_key'),
            ],
        ]);
    }

    public function test_it_can_generate_a_public_url()
    {
        $publicUrl = 'https://account_name.blob.core.windows.net/container/prefix/foobar.txt';

        $this->assertEquals($publicUrl, Storage::disk('azure_blob_storage_disk')->url('foobar.txt'));
    }

    public function test_it_can_generate_a_temporary_url()
    {
        $expirationDate = now()->addHour();

        $temporaryUrl = Storage::disk('azure_blob_storage_disk')->temporaryUrl('foobar.txt', $expirationDate);

        $this->assertStringContainsString('https://account_name.blob.core.windows.net/container/prefix/foobar.txt', $temporaryUrl);

        $this->assertMatchesRegularExpression('/sig=(.*)/', $temporaryUrl);
        $this->assertMatchesRegularExpression('/sp=r/', $temporaryUrl);

        $this->assertMatchesRegularExpression(
            sprintf('/se=%sT%sZ/', $expirationDate->format('Y-m-d'), $expirationDate->format('H:i:s')),
            $temporaryUrl
        );
    }

    public function test_it_can_generate_a_temporary_upload_url()
    {
        $expirationDate = now()->addHour();

        $temporaryUpload = Storage::disk('azure_blob_storage_disk')->temporaryUploadUrl('foobar.txt', $expirationDate);

        $this->assertArrayHasKey('url', $temporaryUpload);
        $this->assertArrayHasKey('headers', $temporaryUpload);

        $this->assertStringContainsString('https://account_name.blob.core.windows.net/container/prefix/foobar.txt', $temporaryUpload['url']);

        $this->assertMatchesRegularExpression('/sig=(.*)/', $temporaryUpload['url']);
        $this->assertMatchesRegularExpression('/sp=w/', $temporaryUpload['url']);

        $this->assertMatchesRegularExpression(
            sprintf('/se=%sT%sZ/', $expirationDate->format('Y-m-d'), $expirationDate->format('H:i:s')),
            $temporaryUpload['url']
        );

        $this->assertArraySubset(['x-ms-blob-type' => 'BlockBlob'], $temporaryUpload['headers']);
    }
}
