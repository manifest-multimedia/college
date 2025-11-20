<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BulkTranscriptExport implements WithMultipleSheets
{
    protected $transcriptDataArray;

    public function __construct($transcriptDataArray)
    {
        $this->transcriptDataArray = $transcriptDataArray;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->transcriptDataArray as $index => $transcriptData) {
            $student = $transcriptData['student'];
            $sheetName = substr($student->student_id, 0, 20); // Limit sheet name length

            $sheets[] = new TranscriptExport($transcriptData);
        }

        return $sheets;
    }
}
