<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FeeCollectionImport;

class FeesChecker extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // seed feecollections table with data from excel
        $filePath = public_path('datasets/dm9_fee_final.xlsx');

        // Import data from the Excel file
        Excel::import(new FeeCollectionImport, $filePath);

        $filePath = public_path('datasets/rgn_7_fee.xlsx');
        Excel::import(new FeeCollectionImport, $filePath);

        $this->command->info('FeeCollection data imported successfully!');
    }
}
