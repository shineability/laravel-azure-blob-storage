<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Connector
{
    private array $connections;

    public function __construct(array $connections = [])
    {
        $this->connections = $connections;
    }

    public function connect(string|array|null $connection = null): ContainerFilesystemFactory
    {
        if (is_array($connection)) {
            return ContainerFilesystemFactory::forConnection($this->createConnection($connection));
        }

        if (!Arr::exists($this->connections, $connection ??= 'default')) {
            throw new InvalidArgumentException("Azure Blob Storage connection [{$connection}] is not configured.");
        }

        return self::connect((array) Arr::get($this->connections, $connection));
    }

    /**
     * @throws \AzureOss\Storage\Blob\Exceptions\InvalidConnectionStringException
     */
    public function container(string $container, string $prefix = '', array $config = []): FilesystemAdapter
    {
        return $this->connect()->container($container, $prefix, $config);
    }

    private function createConnection(array $config): Connection
    {
        return Connection::create($this->convertSnakeCaseKeysToPascalCase($config));
    }

    private function convertSnakeCaseKeysToPascalCase(array $config): array
    {
        return collect($config)
            ->mapWithKeys(fn ($value, $key) => [(string) Str::of($key)->pascal() => $value])
            ->toArray();
    }
}
