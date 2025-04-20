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

### Disk configuration

The driver supports the following disk configuration options:

- `driver` - must be set to `azure_blob_storage`
- `container` - the name of the container
- `prefix` - a prefix to prepend to all paths (**optional**)
- `connection` - the name of the connection or connection config (see [**Connections**](#connections))

Configure the disk in `config/filesystems.php`:

```php
'azure-disk-images' => [
    'driver' => 'azure_blob_storage',
    'container' => 'images',
    'prefix' => 'backup',
    'connection' => [
        'account_name' => env('AZURE_BLOB_STORAGE_ACCOUNT_NAME'),
        'account_key' => env('AZURE_BLOB_STORAGE_ACCOUNT_KEY'),
        'default_endpoints_protocol' => 'http',
        'blob_endpoint' => 'http://127.0.0.1:10000/devstoreaccount1'
        ...
    ],
],
```

And access the disk using the `Storage` facade:

```php
Storage::disk('azure-disk-images')->get('backup/logo.png');
```

### Connections

A connection string is required to access an Azure Blob Storage account and can be configured
using an array containing the following **required** properties:

- `account_name` - name of the storage account
- `account_key` - access key of the storage account

**Optional** properties include:

- `default_endpoints_protocol` - specifies protocol to be used, defaults to `https`
- `endpoint_suffix` - for storage services in different regions, defaults to `core.windows.net`
- `blob_endpoint` - for storage endpoints mapped to a custom domain
- `shared_access_signature` - if you want to connect using a SAS token

For more information on how to configure a connection strings, check out the
[**Azure Storage docs**](https://docs.microsoft.com/en-us/azure/storage/common/storage-configure-connection-string).

You can configure a connection using an array in the driver config in `config/filesystems.php`:

```php
'azure-disk-images' => [
    ...
    'connection' => [
        'account_name' => env('AZURE_BLOB_STORAGE_ACCOUNT_NAME'),
        'account_key' => env('AZURE_BLOB_STORAGE_ACCOUNT_KEY'),
        ...
    ],
],
```

You can also configure one or more connections in `config/filesystems.php` to reuse the same connection(s) for multiple disks:

```php
'azure_blob_storage' => [
    'connections' => [
        'default' => [
            'account_name' => env('AZURE_BLOB_STORAGE_ACCOUNT_NAME'),
            'account_key' => env('AZURE_BLOB_STORAGE_ACCOUNT_KEY'),
            ...
        ],
        'other_connection' => [
            'account_name' => env('AZURE_BLOB_STORAGE_OTHER_ACCOUNT_NAME'),
            'account_key' => env('AZURE_BLOB_STORAGE_OTHER_ACCOUNT_KEY'),
            ...
        ],
        ...
    ],
],
```

To configure a disk, assign the name of the connection to the `connection` key in `config/filesystems.php`:

```php
'azure-disk-images' => [
    'driver' => 'azure_blob_storage',
    'container' => 'images',
    'connection' => 'other_connection' // Use the connection configured in `config/filesystems.php` instead of an array
],
```

If you don't specify a `connection` key in the driver config, the `default` connection will be used.

### Temporary Upload URLs

The driver supports generating temporary upload URLs for a given blob. This is useful when you want to allow users to upload files directly to Azure Blob Storage without exposing your storage account credentials.

```php
use Illuminate\Support\Facades\Storage;

['url' => $url, 'headers' => $headers] = Storage::temporaryUploadUrl(
    'logo.png', now()->addMinutes(5)
);
```

For more information on how to upload blobs using temporary URLs, check the 
[**Azure Storage docs**](https://learn.microsoft.com/en-us/rest/api/storageservices/put-blob).

### Using the `AzureBlobStorage` facade

If you have a storage account with multiple containers, you can use the `AzureBlobStorage` facade to create a filesystem
for each container **at runtime** without having to separately configure a disk in `config/filesystems.php` for each container.

Add a connection to `config/filesystems.php`:

```php
'azure_blob_storage' => [
    'connections' => [
        'your_connection' => [
            'account_name' => env('AZURE_BLOB_STORAGE_ACCOUNT_NAME'),
            'account_key' => env('AZURE_BLOB_STORAGE_ACCOUNT_KEY'),
            ...
        ],
    ],
],
```

Create a container filesystem at runtime:

```php
use Shineability\LaravelAzureBlobStorage\Facades\AzureBlobStorage;

$filesystem = AzureBlobStorage::connect('your_connection')->container('images');

echo $filesystem->url('logo.png'); 
```

If you omit the connection argument in the `connect()` method, the `default` connection will be used:

```php
$filesystem = AzureBlobStorage::connect()->container('images');
```

An even shorter way of creating a container filesystem by connecting to the `default` connection,
is to call the `container()` method directly on the facade:

```php  
$filesystem = AzureBlobStorage::container('images');
```

The facade is bound to the `Connector` class, which is used to create a container filesystems factory for a given connection.

## Testing

```bash
composer test:lint
composer test:types
composer test:unit
```

Run **linting**, **static analysis** and **unit tests** in one go.

```bash
composer test
```

### Run GitHub test workflow locally

You can run the Github workflows locally with [act](https://github.com/nektos/act). To run the tests locally, run:

```
act -j phpunit -P ubuntu-latest=shivammathur/node:latest
```

To run tests for a specific PHP and Laravel version, run:

```
act -j phpunit --matrix php:8.3 --matrix laravel:"11.*" -P ubuntu-latest=shivammathur/node:latest
```

Available `matrix` options are available in the [workflow file](.github/workflows/tests.yml).

## Changelog

Please read the [**CHANGELOG**](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
