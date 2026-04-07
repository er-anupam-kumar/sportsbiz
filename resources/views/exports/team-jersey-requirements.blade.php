<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Jersey Requirements</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { margin: 0 0 6px 0; font-size: 20px; }
        .meta { margin-bottom: 12px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: 700; }
        .empty { margin-top: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Team Jersey Requirements</h1>
    <div class="meta">
        <div>Team: {{ $team->name }}</div>
        <div>Generated: {{ $generatedAt->format('d M Y, h:i A') }}</div>
        <div>Filter: {{ $onlyAdditionalJersey ? 'Only additional jersey requests' : 'All requests' }}</div>
    </div>

    @if($entries->isEmpty())
        <p class="empty">No jersey requirements found for this team.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Nickname</th>
                    <th>Jersey No</th>
                    <th>Additional</th>
                    <th>Additional Qty</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td>{{ ucfirst($entry->request_for ?? 'player') }}</td>
                        <td>{{ $entry->request_for === 'staff' ? ($entry->staff_name ?: $entry->player_name) : $entry->player_name }}</td>
                        <td>{{ $entry->size }}</td>
                        <td>{{ $entry->nickname ?: '-' }}</td>
                        <td>{{ $entry->jersey_number }}</td>
                        <td>{{ $entry->additional_jersey_required ? 'Yes' : 'No' }}</td>
                        <td>{{ $entry->additional_jersey_required ? ($entry->additional_jersey_quantity ?: 0) : '-' }}</td>
                        <td>{{ optional($entry->created_at)->format('d M Y, h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
