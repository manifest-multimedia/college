<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $election->name }} - Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .date {
            font-size: 14px;
            color: #888;
        }
        
        .summary {
            margin-bottom: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        
        .summary-grid {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .summary-box {
            width: 22%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        
        .summary-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .position {
            margin-bottom: 30px;
        }
        
        .position-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .progress {
            width: 80%;
            height: 10px;
            background-color: #eee;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #4285f4;
        }
        
        .progress-winner {
            background-color: #0f9d58;
        }
        
        .percentage {
            float: right;
            width: 15%;
            text-align: right;
        }
        
        .rank-1 {
            color: #f1c40f;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">{{ $election->name }} - Election Results</div>
            <div class="subtitle">{{ $election->description }}</div>
            <div class="date">
                Election Period: {{ $election->start_time->format('M d, Y h:i A') }} to {{ $election->end_time->format('M d, Y h:i A') }}<br>
                Report Generated: {{ now()->format('M d, Y h:i A') }}
            </div>
        </div>
        
        <div class="summary">
            <div class="summary-grid">
                <div class="summary-box">
                    <div class="summary-label">Total Voters</div>
                    <div class="summary-value">{{ $totalVoters }}</div>
                </div>
                <div class="summary-box">
                    <div class="summary-label">Voter Turnout</div>
                    <div class="summary-value">{{ $voterTurnout }}%</div>
                </div>
                <div class="summary-box">
                    <div class="summary-label">Total Votes</div>
                    <div class="summary-value">{{ $totalVotes }}</div>
                </div>
                <div class="summary-box">
                    <div class="summary-label">Positions</div>
                    <div class="summary-value">{{ count($positions) }}</div>
                </div>
            </div>
        </div>
        
        @foreach($positions as $position)
            <div class="position">
                <div class="position-title">{{ $position->name }}</div>
                <p>{{ $position->description }}</p>
                
                @php
                    $totalPositionVotes = $position->candidates->sum('votes_count');
                    $rank = 1;
                @endphp
                
                <table>
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="40%">Candidate</th>
                            <th width="10%">Votes</th>
                            <th width="45%">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($position->candidates->sortByDesc('votes_count') as $candidate)
                            <tr>
                                <td class="{{ $rank === 1 ? 'rank-1' : '' }}">{{ $rank }}</td>
                                <td>{{ $candidate->name }}</td>
                                <td>{{ $candidate->votes_count }}</td>
                                <td>
                                    @php
                                        $percentage = $totalPositionVotes > 0 
                                            ? round(($candidate->votes_count / $totalPositionVotes) * 100, 1) 
                                            : 0;
                                    @endphp
                                    
                                    <div class="progress">
                                        <div class="progress-bar {{ $rank === 1 ? 'progress-winner' : '' }}" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <div class="percentage">{{ $percentage }}%</div>
                                </td>
                            </tr>
                            @php $rank++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
        
        <div class="footer">
            This is an official election results report. Results are final as of the report generation date.
            <br>
            &copy; {{ now()->format('Y') }} {{ config('app.name') }} - College Election System
        </div>
    </div>
</body>
</html>