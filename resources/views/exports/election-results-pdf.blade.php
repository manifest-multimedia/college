<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $election->name }} - Results</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }
        .election-info {
            margin-bottom: 20px;
        }
        .election-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .election-info td {
            padding: 5px;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .position {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .position-header {
            background-color: #f0f0f0;
            padding: 8px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
        }
        .votes-bar {
            height: 20px;
            background-color: #007bff;
            display: inline-block;
        }
        .winner {
            background-color: #28a745;
            color: white;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .page-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Election Results</h1>
        <h2>{{ $election->name }}</h2>
    </div>
    
    <div class="election-info">
        <table>
            <tr>
                <td width="25%"><strong>Description:</strong></td>
                <td width="75%">{{ $election->description }}</td>
            </tr>
            <tr>
                <td><strong>Election Period:</strong></td>
                <td>{{ $election->start_time->format('M d, Y h:i A') }} to {{ $election->end_time->format('M d, Y h:i A') }}</td>
            </tr>
        </table>
    </div>
    
    <div class="summary">
        <table>
            <tr>
                <td width="50%"><strong>Total Votes Cast:</strong> {{ $totalVotes }}</td>
                <td width="50%"><strong>Total Voters:</strong> {{ $totalVoters }}</td>
            </tr>
            <tr>
                <td><strong>Voter Turnout:</strong> {{ round(($totalVoters / max(1, \App\Models\Student::count()) * 100), 1) }}%</td>
                <td><strong>Report Generated:</strong> {{ $exportDate }}</td>
            </tr>
        </table>
    </div>
    
    @foreach($positions as $position)
        <div class="position">
            <div class="position-header">
                <h3>{{ $position->name }}</h3>
                <p>{{ $position->description }}</p>
                <p><strong>Maximum Selections:</strong> {{ $position->max_votes_allowed }}</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th width="10%">Rank</th>
                        <th width="40%">Candidate</th>
                        <th width="15%">Votes</th>
                        <th width="35%">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalPositionVotes = $position->candidates->sum('votes_count');
                        $rank = 1;
                    @endphp
                    
                    @foreach($position->candidates as $candidate)
                        @php
                            $percentage = $totalPositionVotes > 0 
                                ? round(($candidate->votes_count / $totalPositionVotes) * 100, 1) 
                                : 0;
                        @endphp
                        <tr>
                            <td>
                                @if($rank === 1)
                                    <span class="winner">Winner</span>
                                @else
                                    {{ $rank }}
                                @endif
                            </td>
                            <td>
                                {{ $candidate->name }}
                                @if(!$candidate->is_active)
                                    (Inactive)
                                @endif
                            </td>
                            <td>{{ $candidate->votes_count }}</td>
                            <td>
                                {{ $percentage }}%
                            </td>
                        </tr>
                        @php $rank++; @endphp
                    @endforeach
                    
                    @if($totalPositionVotes === 0)
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 15px;">
                                No votes recorded for this position.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endforeach
    
    <div class="page-footer">
        <p>This is an official record of the election results. Generated on {{ $exportDate }}.</p>
    </div>
</body>
</html>