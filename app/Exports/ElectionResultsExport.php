<?php

namespace App\Exports;

use App\Models\Election;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ElectionResultsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected $election;
    protected $positions;
    protected $totalVoters;

    /**
     * Create a new export instance.
     *
     * @param  \App\Models\Election  $election
     * @param  int  $totalVoters
     * @return void
     */
    public function __construct(Election $election, int $totalVoters)
    {
        $this->election = $election;
        // Eager load the position with their candidates and vote counts
        $this->positions = $election->positions()->with(['candidates' => function($query) {
            $query->withCount('votes')->orderByDesc('votes_count');
        }])->orderBy('display_order')->get();
        $this->totalVoters = $totalVoters;
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
            $this->calculateVoterTurnout() . '%',
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
                    $percentage . '%',
                    $candidate->is_active ? 'Active' : 'Inactive',
                ]);
                
                $rank++;
            }
            
            // Add empty row for separation
            $results->push(['', '', '', '', '']);
        }
        
        return $results;
    }

    /**
     * @return array
     */
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
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return $row;
    }

    /**
     * @param Worksheet $sheet
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

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Election Results';
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
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
     *
     * @return float
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