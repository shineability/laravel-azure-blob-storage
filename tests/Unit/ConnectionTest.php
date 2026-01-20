<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shineability\LaravelAzureBlobStorage\Connection;

class ConnectionTest extends TestCase
{
    #[Test]
    public function it_can_be_created(): void
    {
        $connection = Connection::fromArray([
            'AccountName' => 'account_name',
            'AccountKey' => $accountKey = base64_encode('account_key'),
            'EndpointSuffix' => 'core.linux.net',
        ]);

        $this->assertInstanceOf(Connection::class, $connection);

        $connectionString = $connection->toString();

        $this->assertStringContainsString('DefaultEndpointsProtocol=https', $connectionString);
        $this->assertStringContainsString('AccountName=account_name', $connectionString);
        $this->assertStringContainsString("AccountKey={$accountKey}", $connectionString);
        $this->assertStringContainsString('EndpointSuffix=core.linux.net', $connectionString);
    }

    #[Test]
    public function it_requires_valid_keys()
    {
        $this->expectException(InvalidArgumentException::class);

        Connection::fromArray([
            'AccountKey' => 'account_key',
            'EndpointSuffix' => 'core.linux.net',
            'InvalidKey' => 'foobar',
        ]);
    }

    #[Test]
    public function it_requires_an_account_name()
    {
        $this->expectException(InvalidArgumentException::class);

        Connection::fromArray([
            'AccountKey' => 'account_key',
            'EndpointSuffix' => 'core.linux.net',
        ]);
    }

    #[Test]
    public function it_requires_an_account_key()
    {
        $this->expectException(InvalidArgumentException::class);

        Connection::fromArray([
            'AccountName' => 'account_name',
            'EndpointSuffix' => 'core.linux.net',
        ]);
    }
}
