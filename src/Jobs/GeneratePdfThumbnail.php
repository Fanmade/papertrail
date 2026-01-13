<?php

namespace Fanmade\Papertrail\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Fanmade\Papertrail\Contracts\PdfImageGenerator;
use Fanmade\Papertrail\Services\ProcessedPathBuilder;

class GeneratePdfThumbnail implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public string $pdfPath,     // disk-relative
        public string $disk = 'papertrail',
        public ?string $thumbnailPath = null
    ) {}

    public function handle(PdfImageGenerator $generator, ProcessedPathBuilder $paths): void
    {
        $absolute = Storage::disk($this->disk)->path($this->pdfPath);

        // Ensure the processed root directory exists and place the thumb there as "thumb.[ext]"
        $rootDir = $paths->rootDir($this->pdfPath);
        $paths->ensureDir($rootDir);

        $options = [
            'target_dir' => $rootDir,
            'disk' => $paths->disk(),
        ];

        $targetBase = $this->thumbnailPath ? pathinfo($this->thumbnailPath, PATHINFO_FILENAME) : 'thumb';
        $generator->generateThumbnail($absolute, $targetBase, $options);
    }
}
