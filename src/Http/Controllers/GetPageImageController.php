<?php

namespace Vqs\Papertrail\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Vqs\Papertrail\Models\PdfDocument;

class GetPageImageController
{
    public function __invoke(PdfDocument $document, int $page)
    {
        $diskName = (string) config('papertrail.processed.disk', 'papertrail');
        $ext = strtolower((string) config('papertrail.page_images.format', 'png'));
        $file = sprintf('pages/page-%03d.%s', max(1, $page), $ext);
        $path = rtrim($document->path, '/') . '/' . $file;

        $disk = Storage::disk($diskName);
        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path, $file, [
            'Content-Type' => 'image/' . $ext,
            'Cache-Control' => 'private, max-age=3600',
            'Content-Disposition' => 'inline; filename="' . $file . '"',
        ]);
    }
}