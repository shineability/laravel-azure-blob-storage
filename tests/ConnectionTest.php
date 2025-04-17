<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shineability\LaravelAzureBlobStorage\Connection;

class ConnectionTest extends TestCase
{
    public function test_it_can_be_created(): void
    {
        $connection = Connection::create([
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

    public function test_it_requires_valid_keys()
    {
        $this->expectException(InvalidArgumentException::class);

        Connection::create([
            'AccountKey' => 'account_key',
            'EndpointSuffix' => 'core.linux.net',
            'InvalidKey' => 'foobar',
        ]);
    }

    public function test_it_requires_an_account_name()
    {
        $this->expectException(InvalidArgumentException::class);

        Connection::create([
            'AccountKey' => 'account_key',
            'EndpointSuffix' => 'core.linux.net',
        ]);
    }

    public function test_it_requires_an_account_key()
    {
        $this->expectException(InvalidArgumentException::class);

        Connection::create([
            'AccountName' => 'account_name',
            'EndpointSuffix' => 'core.linux.net',
        ]);
    }
}
