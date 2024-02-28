<?php

namespace App\Imports;

use App\Models\Question;
use App\Rules\ValidSlug;
use Illuminate\Support\Str;
use App\Models\QuestionCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class QuestionImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure
{

    use Importable;

    private  int $currentIndex = 0;

    /**
     * @var int
     */
    private int $rows = 0;

    /**
     * @var int
     */
    private int $duplicates = 0;
    /**
     * @var array
     */
    protected array $failures = [];

    /**
     * @param  array  $row
     * @return Question|null
     */
    public function model(array $row): ?Question
    {
        $questionCategory = QuestionCategory::firstOrCreate([
            "title" => $row['category'],
            "slug" => Str::slug($row['category'])
        ]);
        $slug = $row['slug'] ?? Str::slug($row['title']);
        $existingQuestion = Question::where("slug", $slug)->first();
        if ($existingQuestion) {
            ++$this->duplicates;
            return  $existingQuestion;
        }
        ++$this->rows;
        return new Question([
            'category_id' => $questionCategory->id,
            'title' => $row['title'],
            'slug' => $slug,
            'description' => $row['description'],
            'options' => $row['options'],
            'answer' => $row['answer'],
            'weightage' => (string) $row['weightage']
        ]);
    }

    /**
     * @param $data
     * @param $index
     * @return mixed
     */
    public function prepareForValidation($data, $index): mixed
    {
        $this->currentIndex = $index;
        $data['option1'] = (string) ($data['option1'] ?? "");
        $data['option2'] = (string) ($data['option2'] ?? "");
        $data['option3'] = (string) ($data['option3'] ?? "");
        $data['option4'] = (string) ($data['option4'] ?? "");
        $data['option5'] = (string) ($data['option5'] ?? "");
        $data['option6'] = (string) ($data['option6'] ?? "");
        $options = [];
        for ($i = 1; $i <= 6; $i++) {
            if (isset($data["option$i"]) && $data["option$i"] !== "") {
                $options[] = (string) $data["option$i"];
            }
        }
        $data['options'] = $options;
        $data['answer'] = (string) ($data['answer'] ?? "");
        return $data;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'category' => 'required|string|max:255',
            'title' => "string|required|max:255",
            'slug' => [
                'nullable',
                'string',
                'max:255',
                new ValidSlug,
            ],
            'description' => "string|nullable|max:5000",
            'option1' => 'required|string',
            'option2' => 'required|string',
            'option3' => 'string',
            'option4' => 'string',
            'option5' => 'string',
            'option6' => 'string',
            'options' => "required|array",
            'answer' => 'required|string|in_array:' . $this->currentIndex . ".options.*",
            'weightage' => 'required|in:5,10,15',
        ];
    }

    /**
     * @param Failure ...$failures
     * @return void
     */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = [
                'row' => $failure->row(),
                'errors' => $failure->errors(),
            ];
        }
    }

    /**
     * @return array
     */
    public function getFailures(): array
    {
        return $this->failures;
    }

    /**
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rows;
    }

    /**
     * @return int
     */
    public function getDuplicateCount(): int
    {
        return $this->duplicates;
    }
}
