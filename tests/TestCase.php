<?php

namespace Fideism\DatabaseLog\Tests;

use Fideism\DatabaseLog\ServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class
        ];
    }
}