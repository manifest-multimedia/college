<?php

namespace Tests\Unit;

use App\Services\StudentIdGenerationService;
use PHPUnit\Framework\TestCase;

class StudentIdPreservedSequenceTest extends TestCase
{
    public function test_extracts_sequence_from_explicit_old_pattern(): void
    {
        $service = new StudentIdGenerationService;
        $this->assertSame(42, $service->extractPreservedSequence('COL/DA/RGN/26/27/042', '{INSTITUTION}/{PROGRAM}/{YEAR_FULL}/{SEQUENCE_3}'));
        $this->assertSame(42, $service->extractPreservedSequence('COL-0042-RGN', '{INSTITUTION}-{SEQUENCE_4}-{PROGRAM}'));
    }

    public function test_rejects_ambiguous_adjacent_tokens(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new StudentIdGenerationService)->extractPreservedSequence('COL260042', '{INSTITUTION}{YEAR_SIMPLE}{SEQUENCE_4}');
    }

    public function test_rejects_mismatched_source_pattern(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new StudentIdGenerationService)->extractPreservedSequence('OLD/ABC', 'OLD/{SEQUENCE_3}');
    }
}
