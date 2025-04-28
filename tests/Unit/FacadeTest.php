<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests\Unit;

use GrahamCampbell\TestBenchCore\FacadeTrait;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use PHPUnit\Framework\Attributes\Test;
use Shineability\LaravelAzureBlobStorage\Connector;
use Shineability\LaravelAzureBlobStorage\ContainerFilesystemFactory;
use Shineability\LaravelAzureBlobStorage\Facades\AzureBlobStorage;
use Shineability\LaravelAzureBlobStorage\Tests\TestCase;

class FacadeTest extends TestCase
{
    use FacadeTrait;

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
    }

    #[Test]
    public function it_can_create_a_container_filesystem_for_the_default_connection_using_a_shortcut()
    {
        $this->assertInstanceOf(FilesystemContract::class, AzureBlobStorage::container('container'));
    }

    #[Test]
    public function it_can_connect_to_a_file_storage()
    {
        $this->assertInstanceOf(ContainerFilesystemFactory::class, AzureBlobStorage::connect());
        $this->assertInstanceOf(ContainerFilesystemFactory::class, AzureBlobStorage::connect('custom'));
    }

    protected static function getFacadeAccessor(): string
    {
        return Connector::class;
    }

    protected static function getFacadeClass(): string
    {
        return AzureBlobStorage::class;
    }

    protected static function getFacadeRoot(): string
    {
        return Connector::class;
    }
}
