<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AdminTest extends TestCase
{
    public function test_user()
    {
        $user = Admin::where('id', 7)->first();

        $response = $this->actingAs($user, 'admin')
                         ->get('/api/admin/user');

        $response->dd();
        //$response->assertSuccessful();
    }
}
