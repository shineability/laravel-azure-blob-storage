<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage;

use AzureOss\FlysystemAzureBlobStorage\AzureBlobStorageAdapter as FlysystemAdapter;
use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\BlobServiceClient;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;

/**
 * @property FlysystemAdapter $adapter
 */
final class AzureBlobStorageAdapter extends FilesystemAdapter
{
    private BlobContainerClient $containerClient;

    /**
     * @throws \AzureOss\Storage\Blob\Exceptions\InvalidConnectionStringException
     */
    public function __construct(Connection $connection, string $container, string $prefix = '', array $config = [])
    {
        $serviceClient = BlobServiceClient::fromConnectionString($connection->toString());

        $this->containerClient = $serviceClient->getContainerClient($container);

        $adapter = new FlysystemAdapter($this->containerClient, $prefix);

        parent::__construct(new Filesystem($adapter), $adapter, $config);
    }

    public function url($path): string
    {
        return (string) $this->containerClient->getBlobClient($this->prefixer->prefixPath($path))->uri;
    }

    public function temporaryUrl($path, $expiration, array $options = []): string
    {
        return $this->adapter->temporaryUrl($path, $expiration, new Config(['permissions' => 'r', ...$options]));
    }

    public function temporaryUploadUrl($path, $expiration, array $options = []): array
    {
        return [
            'url' => $this->adapter->temporaryUrl($path, $expiration, new Config(['permissions' => 'w'])),
            'headers' => [
                'x-ms-blob-type' => 'BlockBlob',
            ],
        ];
    }
}
