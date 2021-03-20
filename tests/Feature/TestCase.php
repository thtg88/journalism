<?php

namespace Thtg88\Journalism\Tests\Feature;

use Thtg88\Journalism\JournalismServiceProvider;
use Thtg88\Journalism\Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Load package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [JournalismServiceProvider::class];
    }
}
