<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report->getName() }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .meta-info {
            margin-bottom: 20px;
        }
        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-info td {
            padding: 5px 0;
        }
        .meta-info strong {
            display: inline-block;
            width: 150px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ config('branding.institution.name', config('app.name', 'College Management System')) }}</h1>
        <p>{{ $report->getName() }}</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td><strong>Generated On:</strong> {{ \Carbon\Carbon::now()->format('F j, Y, g:i a') }}</td>
                <td><strong>Module:</strong> {{ $report->getModule() }}</td>
            </tr>
            @if(!empty($filters))
                <tr>
                    <td colspan="2">
                        <strong>Filters Applied:</strong>
                        <br>
                        @foreach($filters as $key => $value)
                            @php
                                // Attempt to find the label for this filter
                                $label = $key;
                                foreach($report->getFilters() as $filterOption) {
                                    if ($filterOption['key'] === $key) {
                                        $label = $filterOption['label'];
                                        // If it's a select field, show the option label rather than the value
                                        if (($filterOption['type'] ?? '') === 'select' && isset($filterOption['options'][$value])) {
                                            $value = $filterOption['options'][$value];
                                        }
                                        break;
                                    }
                                }
                            @endphp
                            @if(!empty($value))
                                - {{ $label }}: {{ is_array($value) ? implode(', ', $value) : $value }}<br>
                            @endif
                        @endforeach
                    </td>
                </tr>
            @endif
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                @foreach($columns as $key => $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach($columns as $key => $label)
                        <td>{{ $row[$key] ?? '-' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" style="text-align: center;">No data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated automatically by {{ config('branding.institution.name', config('app.name')) }} Reports Module.
    </div>

</body>
</html>
