<?php

namespace Fanmade\Papertrail\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Fanmade\Papertrail\Models\PdfField;

use function config;
use function response;

class UpdatePdfFieldController
{
    public function __invoke(Request $request, PdfField $field): JsonResponse
    {
        $placeholders = (array) config('papertrail.placeholders', []);
        $validKeys = array_map('strval', array_keys($placeholders));

        $validated = $request->validate([
            // allow null (to clear) or a valid placeholder key
            'assigned_placeholder' => [
                'nullable',
                'string',
                Rule::in($validKeys),
            ],
        ]);

        // Normalize empty string to null
        $newValue = $validated['assigned_placeholder'] ?? null;
        if ($newValue === '') {
            $newValue = null;
        }

        $field->assigned_placeholder = $newValue;
        $field->save();

        return response()->json([
            'data' => [
                'id' => (int) $field->id,
                'assigned_placeholder' => $field->assigned_placeholder,
            ],
        ]);
    }
}
