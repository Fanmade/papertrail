<?php

namespace Vqs\Papertrail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Vqs\Papertrail\Models\PdfDocument;
use Vqs\Papertrail\Services\ProcessedPathBuilder;
use Vqs\Papertrail\Traits\HasDocumentReference;

class FinalizeProcessedPdf implements ShouldQueue
{
    use Dispatchable, HasDocumentReference, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $pdfPath,
        public ?string $documentId = null,
        public string $disk = 'papertrail',
    ) {}

    public function handle(ProcessedPathBuilder $paths): void
    {
        $doc = $this->getDocument($this->documentId, $this->pdfPath);
        if (! $doc) {
            return;
        }

        // Compute processed destination
        $rootDir = $paths->rootDir($this->pdfPath);
        $paths->ensureDir($rootDir);
        $destination = $rootDir.'/document.pdf';

        $sourceDisk = Storage::disk($this->disk);
        $destDisk = Storage::disk($paths->disk());

        // If disks differ, copy then delete; else move/rename
        if ($this->disk !== $paths->disk()) {
            $stream = $sourceDisk->readStream($this->pdfPath);
            if ($stream === false) {
                return;
            }
            $destDisk->put($destination, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            $sourceDisk->delete($this->pdfPath);
        } else {
            // Same disk; perform a move within disk
            $sourceDisk->move($this->pdfPath, $destination);
        }

        // Store only the processed directory path on the document record
        /** @var PdfDocument $doc */
        $doc->update([
            'path' => $rootDir,
        ]);
    }
}
