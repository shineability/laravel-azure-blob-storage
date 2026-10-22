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
    public function it_can_be_created_with_blob_endpoint_and_shared_access_signature(): void
    {
        $connection = Connection::fromArray([
            'BlobEndpoint' => 'https://account_name.blob.core.windows.net/',
            'QueueEndpoint' => 'https://account_name.queue.core.windows.net/',
            'FileEndpoint' => 'https://account_name.file.core.windows.net/',
            'TableEndpoint' => 'https://account_name.table.core.windows.net/',
            'SharedAccessSignature' => 'sv=2023-01-03&ss=b&srt=sco&sp=r&se=2025-01-01',
        ]);

        $connectionString = $connection->toString();

        self::assertStringContainsString('BlobEndpoint=https://account_name.blob.core.windows.net/', $connectionString);
        self::assertStringContainsString('SharedAccessSignature=sv=2023-01-03&ss=b&srt=sco&sp=r&se=2025-01-01', $connectionString);
    }

    #[Test]
    public function it_expands_the_development_storage_shortcut(): void
    {
        $connectionString = Connection::fromString('UseDevelopmentStorage=true')->toString();

        self::assertStringContainsString('DefaultEndpointsProtocol=http;', $connectionString);
        self::assertStringContainsString('AccountName=devstoreaccount1', $connectionString);
        self::assertStringContainsString('AccountKey=', $connectionString);
        self::assertStringContainsString('BlobEndpoint=http://127.0.0.1:10000/devstoreaccount1', $connectionString);
    }

    #[Test]
    public function it_requires_an_account_name_or_blob_endpoint_with_shared_access_signature(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Connection with a "SharedAccessSignature" must include either "AccountName" or "BlobEndpoint".')
        );

        Connection::fromArray([
            'SharedAccessSignature' => 'sv=2023-01-03&ss=b&srt=sco&sp=r&se=2025-01-01',
        ]);
    }

    #[Test]
    public function it_ignores_empty_segments_in_a_connection_string(): void
    {
        $connection = Connection::fromString(
            'AccountName=devstoreaccount1;;AccountKey=account_key;BlobEndpoint=http://127.0.0.1:10000/devstoreaccount1;'
        );

        $connectionString = $connection->toString();

        self::assertStringContainsString('AccountName=devstoreaccount1', $connectionString);
        self::assertStringContainsString('AccountKey=account_key', $connectionString);
        self::assertStringContainsString('BlobEndpoint=http://127.0.0.1:10000/devstoreaccount1', $connectionString);
    }

    #[Test]
    public function it_rejects_a_connection_string_segment_without_a_value(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Expected one of: "AccountName", "AccountKey", "BlobEndpoint", "DefaultEndpointsProtocol", "EndpointSuffix", "FileEndpoint", "QueueEndpoint", "SharedAccessSignature", "TableEndpoint". Got: "invalid_segment"')
        );

        Connection::fromString('AccountName=account_name;invalid_segment;AccountKey=account_key');
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
        $this->expectExceptionObject(
            new InvalidArgumentException('Connection must include either "AccountKey" or "SharedAccessSignature".')
        );

        Connection::fromArray([
            'AccountName' => 'account_name',
            'EndpointSuffix' => 'core.linux.net',
        ]);
    }
}
