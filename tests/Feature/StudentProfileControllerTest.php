<?php

namespace Tests\Feature;

use App\Models\Profile;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class StudentProfileControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @return void
     */
    public function test_student_can_create_profile_on_first_login_without_avatar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('profile.store'), [
            'skills' => 'HTML,CSS,JavaScript',
            'education' => 'This is the education',
            'experience' => 'This is the experience',
            'career' => 'This is the career'
        ]);

        $response->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', 1)
                            ->where('education', "This is the education")
                            ->where('experience', "This is the experience")
                            ->where('career', "This is the career")
                            ->whereAllType([
                                'skills' => 'array',
                            ])
                            ->etc(['avatar_url'])
                    )
                    ->where('message', "Successfully Created")
            );

        $this->assertDatabaseCount('profiles', 1)
            ->assertDatabaseHas('profiles', [
                'user_id' => $user->id,
                'education' => "This is the education",
                'experience' => "This is the experience",
                'career' => "This is the career",
                'skills' => "[\"HTML\",\"CSS\",\"JavaScript\"]",
            ]);
    }

    /**
     * @return void
     */
    public function test_student_can_create_profile_on_first_login_with_avatar(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->image('thumbnail.png');

        $response = $this->actingAs($user)->postJson(route('profile.store'), [
            'skills' => 'HTML,CSS,JavaScript',
            'education' => 'This is the education',
            'experience' => 'This is the experience',
            'career' => 'This is the career',
            'avatar' => $avatar
        ]);

        $profile = Profile::where('user_id', $user->id)->first();

        $response->assertStatus(201);

        Storage::disk('local')->assertExists(str_replace('http://localhost', '', $profile->avatar_url));
    }

    /**
     * @dataProvider validProfileData
     * @return void
     */
    public function test_student_can_update_profile(array $data): void
    {
        $user = User::factory()->create();

        $profile = $this->actingAs($user)->postJson(route('profile.store'), $data);

        $response = $this->actingAs($user)->putJson(route('profile.update', $profile['data']['id']), [
            'skills' => 'HTML,CSS,JavaScript',
            'education' => 'This is the education updated',
            'experience' => 'This is the experience updated',
            'career' => 'This is the career updated'
        ]);

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', 1)
                            ->where('education', "This is the education updated")
                            ->where('experience', "This is the experience updated")
                            ->where('career', "This is the career updated")
                            ->whereAllType([
                                'skills' => 'array',
                            ])
                            ->etc(['avatar_url'])
                    )
                    ->where('message', "Successfully Updated")
            );
    }

    /**
     * @dataProvider validProfileData
     * @return void
     */
    public function test_student_cannot_create_profile_multiple_times(array $data): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('profile.store'), $data);

        $response = $this->actingAs($user)->postJson(route('profile.store'), $data);

        $response->assertStatus(422);
    }

    /**
     * @dataProvider invalidProfileData
     * @return void
     */
    public function test_profile_validations(array $data): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('profile.store'), $data);

        $response->assertStatus(422);
    }

    /**
     * @return array
     */
    public static function validProfileData(): array
    {
        return [
            'valid profile' => [
                [
                    'skills' => 'HTML,CSS,JavaScript',
                    'education' => 'This is the education',
                    'experience' => 'This is the experience',
                    'career' => 'This is the career'
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function invalidProfileData(): array
    {
        return [
            'experience.max:5000' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => 'This is the education',
                    'experience' => str_repeat('a', 5001),
                    'career' => 'This is the career',
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'experience.string' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => 'This is the education',
                    'experience' => 101,
                    'career' => 'This is the career',
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'experience.required' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => 'This is the education',
                    'experience' => '',
                    'career' => 'This is the career',
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'education.required' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => '',
                    'experience' => 'This is the experience',
                    'career' => 'This is the career',
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'skills.array|string' => [
                [
                    'skills' => 123,
                    'education' => '',
                    'experience' => 'This is the experience',
                    'career' => 'This is the career',
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'skills.required' => [
                [
                    'skills' => '',
                    'education' => 'test edu',
                    'experience' => 'This is the experience',
                    'career' => 'This is the career',
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'education.max:5000' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => str_repeat('a', 5001),
                    'experience' => 'This is the experience',
                    'career' => 'This is the career',
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'education.string' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => 101,
                    'experience' => 'This is the experience',
                    'career' => 'This is the career',
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'career.required' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => 'This is the education',
                    'experience' => 'This is the experience',
                    'career' => '',
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'career.max:5000' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => 'This is the education',
                    'experience' => 'This is the experience',
                    'career' => str_repeat('a', 5001),
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'career.string' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => 'This is the education',
                    'experience' => 'This is the experience',
                    'career' => 101,
                    'avatar' => UploadedFile::fake()->image('avatar.jpg'),
                ],
            ],
            'avatar.image' => [
                [
                    'skills' => ['HTML', 'CSS', 'JavaScript'],
                    'education' => 'This is the education',
                    'experience' => 'This is the experience',
                    'career' => 'This is the career',
                    'avatar' => UploadedFile::fake()->create('avatar.txt'),
                ],
            ],
        ];
    }
}
