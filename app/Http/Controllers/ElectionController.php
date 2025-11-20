<?php

namespace App\Http\Controllers;

use App\Exports\ElectionResultsExport;
use App\Models\Election;
use Maatwebsite\Excel\Facades\Excel;

class ElectionController extends Controller
{
    public function index()
    {
        $elections = Election::latest()->paginate(10);

        return view('elections.index', compact('elections'));
    }

    public function show(Election $election)
    {
        $positions = $election->positions()->with('candidates')->get();

        return view('elections.show', compact('election', 'positions'));
    }

    public function results(Election $election)
    {
        $positions = $election->positions()
            ->with(['candidates' => function ($query) {
                $query->withCount('votes');
            }])
            ->get();

        return view('elections.results', compact('election', 'positions'));
    }

    public function exportPdf(Election $election)
    {
        $pdf = \PDF::loadView('exports.election-results-pdf', [
            'election' => $election,
            'positions' => $election->positions()->with(['candidates' => function ($query) {
                $query->withCount('votes');
            }])->get(),
        ]);

        return $pdf->download($election->title.'_results.pdf');
    }

    public function exportExcel(Election $election)
    {
        return Excel::download(new ElectionResultsExport($election), $election->title.'_results.xlsx');
    }
}
