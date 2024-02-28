<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminResultTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Test to verify that admin can view list of result
     */
    public function test_admin_can_view_result(): void
    {
        $this->createAdminUser();

        $response = $this->actingAs($this->user)->get(route('results.index'));

        $response->assertStatus(200);
    }

    /**
     * Test to verify that admin can export result
     */
    public function test_admin_can_export_result(): void
    {
        $this->createAdminUser();

        Storage::fake();

        $response = $this->actingAs($this->user)
            ->get('/api/admin/results?export');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'export_url'
            ]);

        $exportUrl = $response->json('export_url');

        $this->assertNotEmpty($exportUrl);

        $exportFilePath = 'exports/results.xlsx';
        Storage::assertExists($exportFilePath);
    }
}
