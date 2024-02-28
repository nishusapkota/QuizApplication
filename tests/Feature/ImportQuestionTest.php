<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\QuestionCategory;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

class ImportQuestionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test import questions endpoint.
     *
     * @return void
     */
    public function test_admin_can_import_valid_questions()
    {
        $this->createAdminUser();
        $header = ["Category", "Title", "Description", "option1", "option2", "option3", "option4", "option5", "option6", "Answer", "Weightage"];
        $rows =  [
            ["Test Category", "Test", "Test Description", "A", "B", "C", "D", "E", "F", "A", 5],
        ];
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $filePath = storage_path("app/imports/questions.xlsx");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header], NULL, 'A1');
        foreach ($rows as $rowData) {
            $sheet->fromArray($rowData, NULL, 'A' . ($sheet->getHighestRow() + 1));
        }
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);


        $file = new \Illuminate\Http\UploadedFile($filePath, 'questions.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $response = $this->actingAs($this->user)
            ->post('api/admin/import-questions', ['file' => $file]);

        $response->assertStatus(200);

        $response->assertJson(['message' => '1 imported Successfully. 0 duplicate record found']);
        if (File::isDirectory(storage_path("app/imports"))) {
            $files = Storage::files('imports');

            foreach ($files as $file) {
                Storage::delete($file);
            }
        }
    }

    /**
     * Test import questions endpoint.
     *
     * @return void
     */
    public function test_admin_cannot_import_invalid_questions()
    {
        $this->createAdminUser();
        $header = ["Category", "Title", "Description", "option1", "option2", "option3", "option4", "option5", "option6", "Answer", "Weightage"];
        $rows =  [
            ["Test Category", "Test", "Test Description", "A", "B", "C", "D", "E", "F", "A", 30],
        ];
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $filePath = storage_path("app/imports/questions.xlsx");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header], NULL, 'A1');
        foreach ($rows as $rowData) {
            $sheet->fromArray($rowData, NULL, 'A' . ($sheet->getHighestRow() + 1));
        }
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);


        $file = new \Illuminate\Http\UploadedFile($filePath, 'questions.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $response = $this->actingAs($this->user)
            ->post('api/admin/import-questions', ['file' => $file]);

        $response->assertStatus(422);
        if (File::isDirectory(storage_path("app/imports"))) {
            $files = Storage::files('imports');

            foreach ($files as $file) {
                Storage::delete($file);
            }
        }
    }

    /**
     * Test import questions endpoint.
     *
     * @return void
     */
    public function test_admin_cannot_import_duplicate_questions()
    {
        $this->createAdminUser();
        $header = ["Category", "Title", "Description", "option1", "option2", "option3", "option4", "option5", "option6", "Answer", "Weightage"];
        $rows =  [
            ["Test Category", "Test", "Test Description", "A", "B", "C", "D", "E", "F", "A", 5],
            ["Test Category", "Test", "Test Description", "A", "B", "C", "D", "E", "F", "A", 5],
        ];
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $filePath = storage_path("app/imports/questions.xlsx");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header], NULL, 'A1');
        foreach ($rows as $rowData) {
            $sheet->fromArray($rowData, NULL, 'A' . ($sheet->getHighestRow() + 1));
        }
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);


        $file = new \Illuminate\Http\UploadedFile($filePath, 'questions.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $response = $this->actingAs($this->user)
            ->post('api/admin/import-questions', ['file' => $file]);

        $response->assertStatus(200);
        $response->assertJson(['message' => '1 imported Successfully. 1 duplicate record found']);
        if (File::isDirectory(storage_path("app/imports"))) {
            $files = Storage::files('imports');

            foreach ($files as $file) {
                Storage::delete($file);
            }
        }
    }
}
