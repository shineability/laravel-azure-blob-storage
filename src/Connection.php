<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class Connection
{
    private const VALID_KEYS = [
        'AccountName',
        'AccountKey',
        'BlobEndpoint',
        'DefaultEndpointsProtocol',
        'EndpointSuffix',
        'SharedAccessSignature',
    ];

    private const DEFAULTS = [
        'DefaultEndpointsProtocol' => 'https',
        'EndpointSuffix' => 'core.windows.net',
    ];

    /** @var array<string, string> */
    private array $values;

    /**
     * @param  array<string, string>  $values
     */
    private function __construct(array $values)
    {
        Assert::keyExists($values, 'AccountName');

        if (!isset($values['AccountKey']) && !isset($values['SharedAccessSignature'])) {
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
        /** @var array<string, string> $values */
        $values = collect(explode(';', $connectionString))
            ->map(fn (string $pair): array => explode('=', $pair, 2))
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
