<?php

namespace App\Imports;

use App\Models\FeeCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FeeCollectionImport implements ToCollection, WithHeadingRow
{
    /**
     * Handle the collection of rows from the Excel file.
     *
     * @return void
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Avoid duplicate entries based on student_id
            FeeCollection::updateOrCreate(
                ['student_id' => $row['student_id']],
                [
                    'student_name' => $row['student_name'],
                    'is_eligble' => $row['is_eligble'],
                ]
            );
        }
    }
}
