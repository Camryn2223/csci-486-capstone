<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Database\Seeders\PermissionSeeder;

abstract class TestCase extends BaseTestCase
{
    protected bool $seed = true;

    protected string $seeder = PermissionSeeder::class;
}