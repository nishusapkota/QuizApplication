<?php

namespace Tests\Unit\Exports;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class ExportRegisteredStudentTest extends TestCase
{
    use RefreshDatabase;
    public function testStudentExport()
    {
        $this->createAdminUser();

        Storage::fake();

        $response = $this->actingAs($this->user)
            ->get('/api/admin/students?export');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'export_url'
            ]);

        $exportUrl = $response->json('export_url');

        $this->assertNotEmpty($exportUrl);

        $exportFilePath = 'exports/students.xlsx';
        Storage::assertExists($exportFilePath);
    }
}
