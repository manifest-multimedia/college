@props(['data'])

@php
    $headers = $data['headers'] ?? [];
    $rows = $data['rows'] ?? [];
@endphp

@if(!empty($headers) || !empty($rows))
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-sm">
            @if(!empty($headers))
                <thead class="table-light">
                    <tr>
                        @foreach($headers as $header)
                            <th scope="col" style="font-weight: 600; font-size: 14px;">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            @if(!empty($rows))
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td style="font-size: 14px;">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            @endif
        </table>
    </div>
@endif
