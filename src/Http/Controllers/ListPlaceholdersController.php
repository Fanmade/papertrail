<?php

namespace Vqs\Papertrail\Http\Controllers;

use Illuminate\Http\JsonResponse;

use function config;
use function response;

class ListPlaceholdersController
{
    public function __invoke(): JsonResponse
    {
        $placeholders = (array) config('papertrail.placeholders', []);

        // Normalize to array of { key, label }
        $items = [];
        foreach ($placeholders as $key => $label) {
            $items[] = [
                'key' => (string) $key,
                // Run through translator to return a localized label
                'label' => (string) __($label),
            ];
        }

        return response()->json([
            'data' => $items,
        ]);
    }
}
