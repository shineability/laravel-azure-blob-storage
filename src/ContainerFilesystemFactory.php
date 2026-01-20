<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage;

use Illuminate\Filesystem\FilesystemAdapter;

final readonly class ContainerFilesystemFactory
{
    private Connection $connection;

    private function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public static function forConnection(Connection $connection): self
    {
        return new self($connection);
    }

    /**
     * Create a blob storage filesystem for a given container and optional path prefix.
     *
     * @throws \AzureOss\Storage\Blob\Exceptions\InvalidConnectionStringException
     */
    public function container(string $container, string $prefix = '', array $config = []): FilesystemAdapter
    {
        return new AzureBlobStorageAdapter($this->connection, $container, $prefix, $config);
    }
}
