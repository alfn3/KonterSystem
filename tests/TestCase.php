<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    protected bool $bypassAuth = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->bypassAuth) {
            $user = User::factory()->create();
            $this->actingAs($user);
        }
    }
}
