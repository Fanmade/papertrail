<?php

namespace Fanmade\Papertrail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Fanmade\Papertrail\Contracts\PdfImageGenerator;
use Fanmade\Papertrail\Services\ProcessedPathBuilder;

class GeneratePdfThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $pdfPath,     // disk-relative
        public string $disk = 'papertrail',
        public ?string $thumbnailPath = null
    ) {}

    public function handle(PdfImageGenerator $generator, ProcessedPathBuilder $paths): void
    {
        $absolute = Storage::disk($this->disk)->path($this->pdfPath);

        // Ensure processed root directory exists and place the thumb there as "thumb.[ext]"
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
