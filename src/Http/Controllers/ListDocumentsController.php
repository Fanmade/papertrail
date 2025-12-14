<?php

namespace Fanmade\Papertrail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Fanmade\Papertrail\Models\PdfDocument;

use function response;
use function config;
use function route;
use function rtrim;
use function strtolower;

class ListDocumentsController
{
    public function __invoke(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 15);
        $search = (string) $request->get('search', '');

        $query = PdfDocument::query()->orderByDesc('created_at');
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $paginator = $query->paginate($perPage);

        $processedCfg = (array) config('papertrail.processed', []);
        $thumbCfg = (array) config('papertrail.thumb_defaults', []);
        $pagesCfg = (array) config('papertrail.page_images', []);

        $diskName = (string) Arr::get($processedCfg, 'disk', 'papertrail');
        $thumbExt = strtolower((string) Arr::get($thumbCfg, 'format', 'png'));
        $pageExt = strtolower((string) Arr::get($pagesCfg, 'format', 'png'));

        $items = $paginator->getCollection()->map(function (PdfDocument $doc) use ($diskName, $thumbExt, $pageExt) {
            $processedDir = rtrim((string) $doc->path, '/');
            $thumbPath = $processedDir !== '' ? $processedDir . '/thumb.' . $thumbExt : null;
            $firstPagePath = $processedDir !== '' ? $processedDir . '/pages/page-001.' . $pageExt : null;

            $disk = Storage::disk($diskName);
            $thumbUrl = route('papertrail.documents.thumb', $doc);
            $firstPageUrl = route('papertrail.documents.page', [$doc, 1]);
            $thumbAvailable = $thumbPath && $disk->exists($thumbPath);

            return [
                'id' => $doc->id,
                'name' => $doc->name,
                'pages' => $doc->pages,
                'path' => $doc->path,
                'created_at' => optional($doc->created_at)->toISOString(),
                'thumb_path' => $thumbPath,
                'thumb_url' => $thumbUrl,
                'first_page_path' => $firstPagePath,
                'first_page_url' => $firstPageUrl,
                'thumb_available' => $thumbAvailable,
            ];
        })->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
