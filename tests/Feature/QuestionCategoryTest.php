<?php

namespace Tests\Feature;

use App\Models\QuestionCategory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Question;
use Illuminate\Testing\Fluent\AssertableJson;
class QuestionCategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_admin_can_create_questionCategory(): void
    {
        $this->createAdminUser();

        $response = $this->actingAs($this->user)->post(route('question-categories.store'), [
            'title' => 'This is the title',
            'slug' => 'title-slug'
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseCount('question_categories', 1);
    }

    /**
     * @return void
     */
    public function test_admin_can_view_list_of_questionCategories(): void
    {
        $this->createAdminUser();

        $response = $this->actingAs($this->user)->get(route('question-categories.index'));

        $response->assertStatus(200);
    }

    /**
     * @return void
     */
    public function test_admin_can_get_questionCategory_when_given_id(): void
    {
        $this->createAdminUser();

        $response = $this->actingAs($this->user)->post(route('question-categories.store'), [
            'title' => 'This is the title',
            'slug' => 'title-slug'
        ]);

        $response = $this->actingAs($this->user)->get(route('question-categories.show', QuestionCategory::first()));

        $response->assertStatus(200);
    }

    /**
     * @return void
     */
    public function test_admin_can_update_questionCategory(): void
    {
        $this->createAdminUser();

        $this->actingAs($this->user)->post(route('question-categories.store'), [
            'title' => 'This is the title',
            'slug' => 'title-slug'
        ]);

        $response=$this->actingAs($this->user)->put(route('question-categories.update',QuestionCategory::first()),[
            'title' => 'This is the updated title',
            'slug' => 'updated-slug'
        ]);

        $response->assertStatus(200);

        $updated_questionCategory=QuestionCategory::first();
        $this->assertEquals('This is the updated title',$updated_questionCategory->title);
        $this->assertEquals('updated-slug',$updated_questionCategory->slug);
    }

    /**
    * @return void
    */
    public function test_admin_can_delete_questionCategory():void
    {
        $this->createAdminUser();

        $this->actingAs($this->user)->post(route('question-categories.store'), [
            'title' => 'This is the title',
            'slug' => 'title-slug'
        ]);

        $response=$this->actingAs($this->user)->delete(route('question-categories.destroy',QuestionCategory::first()));

        $response->assertStatus(204);

        $this->assertEquals(QuestionCategory::count(),0);
    }
    /**
    * @return void
    */
    public function test_question_category_cannot_be_deleted_if_associated_with_question(): void
    {
        $this->createAdminUser();

        $questionCategory = QuestionCategory::factory()->create();

        $question = Question::factory()->create(['category_id' => $questionCategory->id]);

        $response = $this->actingAs($this->user)->deleteJson(route('question-categories.destroy', $questionCategory));

        $response->assertStatus(200)
                 ->assertJson(
                    fn (AssertableJson $json) => $json->has('error')
                                                        ->where('error', 'Could not delete the Question category.')
                 );

        $this->assertDatabaseHas('question_categories', ['id' => $questionCategory->id]);
    }
}
