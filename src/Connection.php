<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage;

use Stringable;
use Webmozart\Assert\Assert;

final class Connection implements Stringable
{
    private const VALID_KEYS = [
        'AccountName',
        'AccountKey',
        'BlobEndpoint',
        'DefaultEndpointsProtocol',
        'EndpointSuffix',
        'SharedAccessSignature',
    ];

    private array $values;

    private function __construct(array $values)
    {
        Assert::keyExists($values, 'AccountName');
        Assert::keyExists($values, 'AccountKey');

        Assert::allInArray(array_keys($values), self::VALID_KEYS);

        $this->values = $values;
    }

    public static function create(array $values): self
    {
        return new self($values);
    }

    private function defaults(): array
    {
        return [
            'DefaultEndpointsProtocol' => 'https',
            'EndpointSuffix' => 'core.windows.net',
        ];
    }

    public function toString(): string
    {
        return collect($this->defaults())
            ->merge($this->values)
            ->map(fn ($value, $key) => "{$key}={$value}")->implode(';');
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
