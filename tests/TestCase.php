<?php

declare(strict_types=1);

namespace Shineability\LaravelAzureBlobStorage\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Shineability\LaravelAzureBlobStorage\AzureBlobStorageServiceProvider;

abstract class TestCase extends AbstractPackageTestCase
{
    protected static function getServiceProviderClass(): string
    {
        return AzureBlobStorageServiceProvider::class;
    }
}
