<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RegisteredStudentExport implements FromCollection,WithHeadings,WithMapping
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
           'ID','Name',"Email","Skills"
       ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->email,
            implode(",",$row->profile?->skills ?? [])
        ];
    }
}
