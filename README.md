
# Azure Blob Storage filesystem driver for Laravel

This package provides a configurable **Azure Blob Storage** filesystem driver for Laravel allowing the creation of container filesystems at runtime.

## Minimum requirements

- **PHP 8.2** or higher
- **Laravel 11.x** or higher

## Installation

```bash
composer require shineability/laravel-azure-blob-storage
```

## Usage

### Named connections

Define reusable connections in `config/filesystems.php`:

```php
'azure_blob_storage' => [
    'connections' => [
        'default' => [
            'account_name' => env('AZURE_BLOB_STORAGE_ACCOUNT_NAME'),
            'account_key' => env('AZURE_BLOB_STORAGE_ACCOUNT_KEY'),
        ],
        'backup' => [
            'account_name' => env('AZURE_BACKUP_ACCOUNT_NAME'),
            'account_key' => env('AZURE_BACKUP_ACCOUNT_KEY'),
        ],
        // Or use a connection string directly
        'external' => env('AZURE_EXTERNAL_CONNECTION_STRING'),
    ],
],
```

### Disk configuration

Configure a disk in `config/filesystems.php`:

```php
'disks' => [
    // Uses inline connection config
    'azure-images' => [
        'driver' => 'azure_blob_storage',
        'container' => 'images',
        'prefix' => 'backup',
        'connection' => [
            'account_name' => env('AZURE_BLOB_STORAGE_ACCOUNT_NAME'),
            'account_key' => env('AZURE_BLOB_STORAGE_ACCOUNT_KEY'),
        ],
    ],

    // Uses a connection string directly
    'azure-backups' => [
        'driver' => 'azure_blob_storage',
        'container' => 'backups',
        'connection' => env('AZURE_BLOB_STORAGE_CONNECTION_STRING'),
    ],

    // Uses a named connection
    'azure-documents' => [
        'driver' => 'azure_blob_storage',
        'container' => 'documents',
        'connection' => 'backup',  // References named connection
    ],

    // Uses 'default' named connection when omitted
    'azure-uploads' => [
        'driver' => 'azure_blob_storage',
        'container' => 'uploads',
    ],
],
```

Access the disk using the `Storage` facade:

```php
Storage::disk('azure-images')->put('logo.png', $contents);
```

### Connection options

| Property                     | Required | Default            | Description                              |
|------------------------------|----------|--------------------|------------------------------------------|
| `account_name`               | Yes      | -                  | Storage account name                     |
| `account_key`                | Yes*     | -                  | Storage account access key               |
| `shared_access_signature`    | Yes*     | -                  | SAS token (alternative to `account_key`) |
| `default_endpoints_protocol` | No       | `https`            | Protocol to use                          |
| `endpoint_suffix`            | No       | `core.windows.net` | Regional endpoint suffix                 |
| `blob_endpoint`              | No       | -                  | Custom domain endpoint                   |

\* Either `account_key` or `shared_access_signature` is **required**.

For more information on connection strings, see the [**Azure Storage docs**](https://docs.microsoft.com/en-us/azure/storage/common/storage-configure-connection-string).

### Temporary upload URLs

Generate temporary upload URLs to allow direct uploads to Azure Blob Storage without exposing credentials:

```php
use Illuminate\Support\Facades\Storage;

['url' => $url, 'headers' => $headers] = Storage::disk('azure-images')->temporaryUploadUrl(
    'logo.png', now()->addMinutes(5)
);
```

For more information, see the [**Azure Storage docs**](https://learn.microsoft.com/en-us/rest/api/storageservices/put-blob).

### Runtime container access

Use the `AzureBlobStorage` facade to create container filesystems at runtime without configuring separate disks:

```php
use Shineability\LaravelAzureBlobStorage\Facades\AzureBlobStorage;

// Use the 'default' named connection
$filesystem = AzureBlobStorage::container('images');

// Or explicitly connect to a named connection
$filesystem = AzureBlobStorage::connect('backup')->container('images');

$filesystem->put('photo.jpg', $contents);

echo $filesystem->url('photo.jpg');
```

You can also connect using an inline **config array**:

```php
$filesystem = AzureBlobStorage::connect([
    'account_name' => env('AZURE_BLOB_STORAGE_ACCOUNT_NAME'),
    'account_key' => env('AZURE_BLOB_STORAGE_ACCOUNT_KEY'),
])->container('images');
```

Or using a **connection string**:

```php
$filesystem = AzureBlobStorage::connect(env('AZURE_BLOB_STORAGE_CONNECTION_STRING'))
    ->container('images');
```

## Testing

Run unit tests:

```bash
composer test:unit
```

Run feature tests (requires [Azurite](https://github.com/Azure/Azurite) on port 10000):

```bash
composer test:feature
```

Run static analysis:

```bash
composer test:types
```

Run linting:

```bash
composer lint
```

Run all quality checks (unit tests, feature tests, static analysis, linting):

```bash
composer test
```

### Run tests with Docker

For consistent testing with code coverage, use Docker via the Makefile:

```bash
# Build the Docker image (runs automatically when needed)
make build

# Run all quality checks
make test

# Run unit tests only
make test-unit

# Run feature tests only
make test-feature

# Run tests with coverage report
make test-coverage

# Run static analysis
make test-types

# Run linting
make lint

# Clean up (remove containers and build artifacts)
make clean
```

### Run GitHub workflow locally

Run the GitHub workflows locally with [**act**](https://github.com/nektos/act):

```bash
act -j unit-tests -P ubuntu-latest=shivammathur/node:latest
```

Run tests for a specific PHP and Laravel version:

```bash
act -j unit-tests --matrix php:8.3 --matrix laravel:"11.*" -P ubuntu-latest=shivammathur/node:latest
```

Available matrix options are in the [**workflow file**](.github/workflows/unit-tests.yml).

## Changelog

Please see the [**CHANGELOG**](CHANGELOG.md) for more information on what has changed recently.

## Alternatives

- [azure-oss/azure-storage-php-adapter-laravel](https://github.com/Azure-OSS/azure-storage-php-adapter-laravel)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
