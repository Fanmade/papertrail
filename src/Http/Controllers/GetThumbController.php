<?php

namespace Fanmade\Papertrail\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Fanmade\Papertrail\Models\PdfDocument;
use function strtolower;

class GetThumbController
{
    public function __invoke(PdfDocument $document)
    {
        $diskName = (string) config('papertrail.processed.disk', 'papertrail');
        $ext = strtolower((string) config('papertrail.thumb_defaults.format', 'png'));
        $path = rtrim($document->path, '/') . "/thumb.$ext";

        $disk = Storage::disk($diskName);
        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path, 'thumb.'. $ext, [
            'Content-Type' => 'image/'.$ext,
            'Cache-Control' => 'private, max-age=3600',
            'Content-Disposition' => 'inline; filename="thumb.'.$ext.'"',
        ]);
    }
}