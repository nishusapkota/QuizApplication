<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    public function test_new_users_can_register(): void
    {
        $user = User::factory()->make();
        $response = $this->post(route("register"), [
            'name' => $user->name,
            'email' => $user->email ,
            'password' => 'Proshore@123',
            'password_confirmation' => 'Proshore@123',
        ]);

        $response->assertNoContent();
    }
}
