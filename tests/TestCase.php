<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Pengaman: RefreshDatabase menghapus semua tabel, jangan pernah jalan di database dev.
        $database = config('database.connections.' . config('database.default') . '.database');
        if (!str_ends_with((string) $database, '_test')) {
            $this->fail("Test menolak berjalan pada database '{$database}'. Gunakan database berakhiran _test.");
        }
    }
}
