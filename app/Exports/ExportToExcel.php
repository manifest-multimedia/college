<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;



class ExportToExcel implements FromCollection, WithHeadings, ShouldAutoSize
{

    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data;
    protected $fieldNames;
    protected $columnNames;


    public function __construct($data, array $fieldNames, array $columnNames)
    {
        // dd($data);
        $this->data = $data;
        $this->fieldNames = $fieldNames;
        $this->columnNames = $columnNames;
    }
    public function collection()
    {

        return collect($this->data)->map(function ($item) {
            return collect($item)->only($this->fieldNames);
        });
    }

    public function headings(): array
    {

        // Use the selected fields as headings
        // return $this->selectedFields ?? [];

        return $this->columnNames;
    }
}
