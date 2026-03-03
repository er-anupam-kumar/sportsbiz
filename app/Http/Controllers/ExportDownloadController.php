<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportDownloadController extends Controller
{
    public function __invoke(Request $request)
    {
        $path = (string) $request->query('path');

        if (! str_starts_with($path, 'exports/')) {
            abort(403, 'Invalid export path.');
        }

        $allowed = AppNotification::query()
            ->where('user_id', auth()->id())
            ->where('type', 'export_ready')
            ->where('data->path', $path)
            ->exists();

        if (! $allowed) {
            abort(403, 'You are not authorized to access this export.');
        }

        if (! Storage::disk('local')->exists($path)) {
            abort(404, 'Export file not found.');
        }

        return response()->download(Storage::disk('local')->path($path));
    }
}
