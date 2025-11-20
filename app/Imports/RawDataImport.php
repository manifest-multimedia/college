<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class RawDataImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // This class is only used to read raw data, no processing needed
    }
}
