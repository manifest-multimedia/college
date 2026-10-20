<?php

namespace App\Imports;

use App\Services\StudentImportDateParser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/** Read the complete mapped date column, in chunks, before any student writes. */
class StudentDateFormatInspector implements ToCollection, WithChunkReading, WithHeadingRow
{
    public array $analysis;

    private StudentImportDateParser $parser;

    public function __construct(private string $column, private int $headerRow = 1)
    {
        $this->parser = new StudentImportDateParser;
        $this->analysis = $this->parser->inspect([]);
    }

    public function collection(Collection $rows): void
    {
        $this->analysis = $this->parser->combine($this->analysis, $this->parser->inspect(
            $rows->map(fn ($row) => $row[$this->column] ?? null)
        ));
    }

    public function headingRow(): int
    {
        return $this->headerRow;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
