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
    public function it_can_be_created_with_account_key(): void
    {
        $connection = Connection::fromArray([
            'AccountName' => 'account_name',
            'AccountKey' => $accountKey = base64_encode('account_key'),
            'EndpointSuffix' => 'core.linux.net',
        ]);

        self::assertInstanceOf(Connection::class, $connection);

        $connectionString = $connection->toString();

        self::assertStringContainsString('DefaultEndpointsProtocol=https', $connectionString);
        self::assertStringContainsString('AccountName=account_name', $connectionString);
        self::assertStringContainsString("AccountKey={$accountKey}", $connectionString);
        self::assertStringContainsString('EndpointSuffix=core.linux.net', $connectionString);
    }

    #[Test]
    public function it_can_be_created_with_shared_access_signature(): void
    {
        $connection = Connection::fromArray([
            'AccountName' => 'account_name',
            'SharedAccessSignature' => 'sv=2023-01-03&ss=b&srt=sco&sp=r&se=2025-01-01',
            'EndpointSuffix' => 'core.windows.net',
        ]);

        self::assertInstanceOf(Connection::class, $connection);

        $connectionString = $connection->toString();

        self::assertStringContainsString('AccountName=account_name', $connectionString);
        self::assertStringContainsString('SharedAccessSignature=sv=2023-01-03&ss=b&srt=sco&sp=r&se=2025-01-01', $connectionString);
    }

    #[Test]
    public function it_requires_valid_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Connection::fromArray([
            'AccountName' => 'account_name',
            'AccountKey' => 'account_key',
            'EndpointSuffix' => 'core.linux.net',
            'InvalidKey' => 'foobar',
        ]);
    }

    #[Test]
    public function it_requires_an_account_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Connection::fromArray([
            'AccountKey' => 'account_key',
            'EndpointSuffix' => 'core.linux.net',
        ]);
    }

    #[Test]
    public function it_requires_either_account_key_or_sas_token(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Connection must include either "AccountKey" or "SharedAccessSignature".');

        Connection::fromArray([
            'AccountName' => 'account_name',
            'EndpointSuffix' => 'core.linux.net',
        ]);
    }
}
