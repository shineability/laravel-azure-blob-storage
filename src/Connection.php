<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class Connection
{
    private const VALID_KEYS = [
        'AccountName',
        'AccountKey',
        'BlobEndpoint',
        'DefaultEndpointsProtocol',
        'EndpointSuffix',
        'FileEndpoint',
        'QueueEndpoint',
        'SharedAccessSignature',
        'TableEndpoint',
    ];

    private const DEFAULTS = [
        'DefaultEndpointsProtocol' => 'https',
        'EndpointSuffix' => 'core.windows.net',
    ];

    private const DEVELOPMENT_STORAGE_SHORTCUT = 'UseDevelopmentStorage=true';

    // Well-known Azurite emulator account, see https://learn.microsoft.com/en-us/azure/storage/common/storage-use-azurite
    private const DEVELOPMENT_STORAGE = [
        'DefaultEndpointsProtocol' => 'http',
        'AccountName' => 'devstoreaccount1',
        'AccountKey' => 'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==',
        'BlobEndpoint' => 'http://127.0.0.1:10000/devstoreaccount1',
    ];

    /** @var array<string, string> */
    private array $values;

    /**
     * @param  array<string, string>  $values
     */
    private function __construct(array $values)
    {
        if (isset($values['AccountKey'])) {
            Assert::keyExists($values, 'AccountName');
        } elseif (isset($values['SharedAccessSignature'])) {
            if (!isset($values['AccountName']) && !isset($values['BlobEndpoint'])) {
                throw new InvalidArgumentException(
                    'Connection with a "SharedAccessSignature" must include either "AccountName" or "BlobEndpoint".'
                );
            }
        } else {
            throw new InvalidArgumentException(
                'Connection must include either "AccountKey" or "SharedAccessSignature".'
            );
        }

        foreach (array_keys($values) as $key) {
            Assert::inArray($key, self::VALID_KEYS);
        }

        $this->values = $values;
    }

    public static function fromString(string $connectionString): self
    {
        if ($connectionString === self::DEVELOPMENT_STORAGE_SHORTCUT) {
            return new self(self::DEVELOPMENT_STORAGE);
        }

        /** @var array<string, string> $values */
        $values = collect(explode(';', $connectionString))
            ->filter(fn (string $pair): bool => $pair !== '')
            ->map(fn (string $pair): array => explode('=', $pair, 2) + [1 => ''])
            ->mapWithKeys(fn (array $pair): array => [$pair[0] => $pair[1]])
            ->toArray();

        return new self($values);
    }

    /**
     * @param  array<string, string>  $connection
     */
    public static function fromArray(array $connection): self
    {
        return new self($connection);
    }

    public static function isValidConnectionString(string $connectionString): bool
    {
        return preg_match_all('/([^=;]+)=([^;]+)/', $connectionString) > 0;
    }

    public function toString(): string
    {
        return collect(self::DEFAULTS)
            ->merge($this->values)
            ->map(fn (string $value, string $key): string => "{$key}={$value}")
            ->implode(';');
    }
}
