<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Facades;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Facade;
use Shineability\LaravelAzureBlobStorage\Connector;
use Shineability\LaravelAzureBlobStorage\ContainerFilesystemFactory;

/**
 * @method static ContainerFilesystemFactory connect(string|array|null $connection = null)
 * @method static FilesystemAdapter container(string $path, string $prefix = '', array $config = [])
 */
class AzureBlobStorage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Connector::class;
    }
}
