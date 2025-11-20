<?php

namespace App\Exports;

use App\Models\Election;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ElectionResultsExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $election;

    protected $positions;

    protected $totalVoters;

    /**
     * Create a new export instance.
     *
     * @param  int  $electionId
     * @return void
     */
    public function __construct($electionId)
    {
        $this->election = Election::findOrFail($electionId);
        // Eager load the position with their candidates and vote counts
        $this->positions = $this->election->positions()->with(['candidates' => function ($query) {
            $query->withCount('votes')->orderByDesc('votes_count');
        }])->orderBy('display_order')->get();
        $this->totalVoters = \App\Models\Student::count();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $results = collect([]);

        // Add election info as first rows
        $results->push([
            'Election Name',
            $this->election->name,
            '',
            '',
            '',
        ]);

        $results->push([
            'Description',
            $this->election->description,
            '',
            '',
            '',
        ]);

        $results->push([
            'Start Time',
            $this->election->start_time->format('M d, Y h:i A'),
            '',
            'End Time',
            $this->election->end_time->format('M d, Y h:i A'),
        ]);

        $results->push([
            'Total Voters',
            $this->totalVoters,
            '',
            'Voter Turnout',
            $this->calculateVoterTurnout().'%',
        ]);

        $results->push([
            'Report Generated',
            now()->format('M d, Y h:i A'),
            '',
            '',
            '',
        ]);

        // Add empty row for separation
        $results->push(['', '', '', '', '']);

        // Process each position and its candidates
        foreach ($this->positions as $position) {
            // Position header
            $results->push([
                'Position',
                $position->name,
                '',
                '',
                '',
            ]);

            $results->push([
                'Description',
                $position->description,
                '',
                '',
                '',
            ]);

            // Check if this is a single-candidate position
            if ($position->hasSingleCandidate()) {
                $yesNoResults = $position->getYesNoVotes();

                if ($yesNoResults) {
                    $candidate = $yesNoResults['candidate'];

                    // Add YES/NO vote information
                    $results->push([
                        'Candidate',
                        $candidate->name,
                        '',
                        'Status',
                        $yesNoResults['has_won'] ? 'APPROVED' : 'REJECTED',
                    ]);

                    $results->push([
                        'YES Votes',
                        $yesNoResults['yes_votes'],
                        $yesNoResults['yes_percent'].'%',
                        'NO Votes',
                        $yesNoResults['no_votes'],
                        $yesNoResults['no_percent'].'%',
                    ]);

                    $results->push([
                        'Total Votes',
                        $yesNoResults['total_votes'],
                        '',
                        'Result',
                        $yesNoResults['has_won']
                            ? 'Approved with '.$yesNoResults['yes_percent'].'% YES votes'
                            : 'Rejected with '.$yesNoResults['no_percent'].'% NO votes',
                    ]);
                } else {
                    // No votes yet for this single-candidate position
                    $results->push([
                        'Note',
                        'No votes recorded for this position yet.',
                        '',
                        '',
                        '',
                    ]);
                }
            } else {
                // Standard multiple-candidate position
                $results->push([
                    'Rank',
                    'Candidate Name',
                    'Votes',
                    'Percentage',
                    'Status',
                ]);

                $totalPositionVotes = $position->candidates->sum('votes_count');
                $rank = 1;

                foreach ($position->candidates as $candidate) {
                    $percentage = $totalPositionVotes > 0
                        ? round(($candidate->votes_count / $totalPositionVotes) * 100, 1)
                        : 0;

                    $results->push([
                        $rank,
                        $candidate->name,
                        $candidate->votes_count,
                        $percentage.'%',
                        $candidate->is_active ? 'Active' : 'Inactive',
                    ]);

                    $rank++;
                }
            }

            // Add empty row for separation
            $results->push(['', '', '', '', '']);
        }

        return $results;
    }

    public function headings(): array
    {
        return [
            'Election Results',
            '',
            '',
            '',
            '',
        ];
    }

    /**
     * @param  mixed  $row
     */
    public function map($row): array
    {
        return $row;
    }

    /**
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as title
            1 => [
                'font' => ['bold' => true, 'size' => 16],
            ],
            // Style for the position headers
            'A' => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function title(): string
    {
        return 'Election Results';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);

                // Merge cells for the title
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');

                // Election information formatting
                $sheet->getStyle('A1:E6')->getFont()->setBold(true);
                $sheet->getStyle('A1:E1')->getFont()->setSize(16);
            },
        ];
    }

    /**
     * Calculate voter turnout percentage
     */
    protected function calculateVoterTurnout(): float
    {
        if ($this->totalVoters == 0) {
            return 0;
        }

        // Count unique voters
        $uniqueVoters = $this->election->votes()->distinct('student_id')->count('student_id');

        return round(($uniqueVoters / $this->totalVoters) * 100, 1);
    }
}
