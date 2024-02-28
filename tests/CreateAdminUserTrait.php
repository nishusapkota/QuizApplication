<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreateAdminUserTrait
{
    public User $user;

    public function createAdminUser(): void
    {
        $this->user = User::factory()->create([
            'name' => 'Cesar Morales',
            'email' => 'admin@skillshore.com',
            'password' => Hash::make('Sk!||P@r8:8000'),
            'user_type' => 'admin'
        ]);
    }
}
