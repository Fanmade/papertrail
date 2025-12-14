<?php

return [
    'thumb_driver' => env('PAPERTRAIL_THUMBNAIL_DRIVER', 'imagick'), // imagick|poppler|spatie
    'fields_driver' => env('PAPERTRAIL_FIELDS_DRIVER', 'python'),
    'form_filler' => env('PAPERTRAIL_FORM_FILLER', 'python'),
    'thumb_disk' => env('PAPERTRAIL_THUMBNAIL_DISK', 'papertrail'),
    'thumb_path' => env('PAPERTRAIL_THUMBNAIL_PATH', 'thumbnails/pdfs'),

    'thumb_defaults' => [
        'width' => 600,      // px
        'height' => 800,     // px (kept aspect, acts as max bounds)
        'format' => 'png',   // png|jpg
        'quality' => 85,     // when jpg
        'density' => 144,    // render DPI (higher = sharper, slower)
        'background' => '#FFFFFF',
    ],

    // Base directory for processed PDFs
    'processed' => [
        // disk to use for all processed assets (document.pdf, thumb, pages)
        'disk' => env('PAPERTRAIL_PROCESSED_DISK', 'papertrail'),
        // base path under the disk where processed items are stored
        'base_path' => env('PAPERTRAIL_PROCESSED_BASE', 'processed'),
        // PHP date() format used to create year-month folder names
        // Example: 'Ym' => 202512
        'date_folder_format' => env('PAPERTRAIL_PROCESSED_DATE_FORMAT', 'Ym'),
    ],

    // Per-page image generation
    'page_images' => [
        'disk' => env('PAPERTRAIL_PAGE_IMG_DISK', 'papertrail'),
        'path' => env('PAPERTRAIL_PAGE_IMG_PATH', 'pages/pdfs'),
        'dpi' => (int) env('PAPERTRAIL_PAGE_IMG_DPI', 200), // readable text on A4
        'format' => env('PAPERTRAIL_PAGE_IMG_FORMAT', 'png'), // png|jpg
        'quality' => (int) env('PAPERTRAIL_PAGE_IMG_QUALITY', 90), // when jpg
        'background' => env('PAPERTRAIL_PAGE_IMG_BG', '#FFFFFF'),
        'max_pages' => (int) env('PAPERTRAIL_PAGE_IMG_MAX', 200),
    ],

    // These placeholders will be available for selection on each form-field the PDFs
    // will provide. Selecting one will allow the system to replace that
    // placeholder with actual content for that form-field.
    'placeholders' => [
//        'full_name', => __('Full name'),
    ],

];
