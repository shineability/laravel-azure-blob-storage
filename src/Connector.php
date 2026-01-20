<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Connector
{
    /**
     * @param  array<string, string|array<string, string>>  $connections
     */
    public function __construct(
        private array $connections = []
    ) {}

    public function connect(string|array|null $connection = null): ContainerFilesystemFactory
    {
        if (is_array($connection)) {
            return ContainerFilesystemFactory::forConnection(
                Connection::fromArray($this->convertSnakeCaseKeysToPascalCase($connection))
            );
        }

        if (is_string($connection) && Connection::isValidConnectionString($connection)) {
            return ContainerFilesystemFactory::forConnection(Connection::fromString($connection));
        }

        $name = $connection ?? 'default';

        if (!array_key_exists($name, $this->connections)) {
            throw new InvalidArgumentException("Azure Blob Storage connection [{$name}] is not configured.");
        }

        return $this->connect($this->connections[$name]);
    }

    /**
     * @throws \AzureOss\Storage\Blob\Exceptions\InvalidConnectionStringException
     */
    public function container(string $container, string $prefix = '', array $config = []): FilesystemAdapter
    {
        return $this->connect()->container($container, $prefix, $config);
    }

    /**
     * @param  array<string, string>  $config
     * @return array<string, string>
     */
    private function convertSnakeCaseKeysToPascalCase(array $config): array
    {
        /** @var array<string, string> */
        return collect($config)
            ->mapWithKeys(fn (string $value, string $key): array => [Str::pascal($key) => $value])
            ->toArray();
    }
}
