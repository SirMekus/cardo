<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_creating_permissions()
    {
        $user = User::where('email', 'sirmekus@come.com')->first();

        $response = $this->actingAs($user)
                         ->post('/api/dashboard/set-permission', ['permission'=>1, 'role'=>[1,2,10]]);

        //$response->dd();
        $response->assertSuccessful();
    }
}
