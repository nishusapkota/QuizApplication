<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QuestionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        return $this->records;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            "ID",
            "Title",
            "Slug",
            "Description",
            "Options",
            "Answer",
            "Status",
            "Weightage",
            'QuestionCategory'
        ];
    }

    /**
     * @param  $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->title,
            $row->slug,
            $row->description,
            implode(',', $row->options),
            $row->answer,
            $row->status? 'Active':'Inactive',
            $row->weightage,
            $row->category->title
        ];
    }

}
