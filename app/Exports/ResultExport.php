<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class ResultExport implements FromCollection,WithHeadings,WithMapping
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

    public function headings(): array
    {
        return [
            'ID','Name',"Quiz","Passed","Total Question","Total Answered","Total Right Answer","Total Time"
        ];
    }
    public function map($row): array
    {
        return [
            $row->id,
            $row->user->name,
            $row->quiz->title,
            $row->passed ? "Pass" : "Fail",
            $row->total_question ?: "0",
            $row->total_answered ?: "0",
            $row->total_right_answer ?: "0",
            $row->total_time
        ];
    }
}
