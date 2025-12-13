<?php

namespace Fanmade\Papertrail\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Fanmade\Papertrail\Models\PdfDocument;
use Fanmade\Papertrail\Models\PdfField;
use Fanmade\Papertrail\Models\PdfPage;
use Fanmade\Papertrail\Support\FieldCoordinateNormalizer;

use function response;

class ListPdfFieldsController
{
    public function __invoke(Request $request, PdfDocument $document): JsonResponse
    {
        // Load page dimensions for percentage normalization
        $pages = PdfPage::query()
            ->where('document_id', $document->id)
            ->get(['page_number', 'width_pt', 'height_pt', 'width_px', 'height_px'])
            ->keyBy('page_number');

        $fields = PdfField::query()
            ->where('document_id', $document->id)
            ->orderBy('page_number')
            ->get();

        $origin = (string) (config('papertrail.fields.coordinates.origin') ?? 'top-left');
        $items = $fields->map(function (PdfField $field) use ($pages, $origin) {
            $pageInfo = $pages->get($field->page_number);
            // Prefer points (pt) since PDF field coordinates are in pt; fall back to px if pt is missing
            $widthTotal = (int) ($pageInfo->width_pt ?? 0);
            $heightTotal = (int) ($pageInfo->height_pt ?? 0);
            if ($widthTotal <= 0 || $heightTotal <= 0) {
                $widthTotal = (int) ($pageInfo->width_px ?? 0);
                $heightTotal = (int) ($pageInfo->height_px ?? 0);
            }

            $percent = FieldCoordinateNormalizer::toPercentages(
                x: (float) $field->x,
                y: (float) $field->y,
                width: (float) $field->width,
                height: (float) $field->height,
                pageWidth: $widthTotal,
                pageHeight: $heightTotal,
                origin: $origin,
            );

            return [
                'id' => (int) $field->id,
                'document_id' => (string) $field->document_id,
                'name' => (string) $field->name,
                'value' => $field->value,
                'type' => $field->type,
                'page_number' => (int) $field->page_number,
                'x' => (float) $field->x,
                'y' => (float) $field->y,
                'width' => (float) $field->width,
                'height' => (float) $field->height,
                'assigned_placeholder' => $field->assigned_placeholder,
                'percent' => $percent,
            ];
        })->values();

        return response()->json([
            'data' => $items,
        ]);
    }
}
