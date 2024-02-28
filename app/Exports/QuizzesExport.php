<?php

namespace App\Exports;

use App\Models\Quiz;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class QuizzesExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $quizzes;

    public function __construct($quizzes)
    {
        $this->quizzes = $quizzes;
    }

    public function collection(): Collection
    {
        return $this->quizzes;
    }

    public function headings(): array
    {
        return [
            "ID",
            'Title',
            'Slug',
            'Category',
            'Description',
            'Time',
            'Retry After',
            'Status',
            'Pass Percentage'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->title,
            $row->slug,
            $row->category_id,
            $row->thumbnail,
            $row->description,
            $row->time,
            $row->retry_after,
            $row->status ? "Active" : "Inactive",
            $row->pass_percentage
        ];
    }
}
