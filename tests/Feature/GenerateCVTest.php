<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateCVTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_student_cannot_generate_cv_without_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
        ]);

        $response = $this->actingAs($user)->getJson(route('generate-cv'));

        $response->assertStatus(503)
            ->assertJson(['message' => 'Please create profile to generate CV']);
    }

    /**
     * @return void
     */
    public function test_unauthenticated_student_cannot_generate_cv(): void
    {
        $response = $this->getJson(route('generate-cv'));

        $response->assertStatus(401)
            ->assertJson(["message" => "Unauthenticated."]);
    }
}
