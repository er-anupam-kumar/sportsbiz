<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tournament Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
        h1 { margin-bottom: 4px; }
        .meta { color: #6b7280; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Tournament Report</h1>
    <div class="meta">{{ $tournament->name }} | Generated {{ now()->toDateTimeString() }}</div>
    <table>
        <thead>
            <tr><th>Metric</th><th>Value</th></tr>
        </thead>
        <tbody>
            <tr><td>Total Teams</td><td>{{ $teams }}</td></tr>
            <tr><td>Total Players</td><td>{{ $players }}</td></tr>
            <tr><td>Sold Players</td><td>{{ $soldPlayers }}</td></tr>
            <tr><td>Total Bids</td><td>{{ $bids }}</td></tr>
        </tbody>
    </table>
</body>
</html>
