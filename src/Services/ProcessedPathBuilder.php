<?php

namespace Fanmade\Papertrail\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;

use function config;
use function pathinfo;
use function rtrim;

class ProcessedPathBuilder
{
    private string $disk;
    private string $basePath;
    private string $dateFolderFormat;

    public function __construct(
        ?string $disk = null,
        ?string $basePath = null,
        ?string $dateFolderFormat = null,
    ) {
        $cfg = (array) config('papertrail.processed', []);
        $this->disk = (string) ($disk ?? ($cfg['disk'] ?? 'papertrail'));
        $this->basePath = (string) ($basePath ?? ($cfg['base_path'] ?? 'processed'));
        $this->dateFolderFormat = (string) ($dateFolderFormat ?? ($cfg['date_folder_format'] ?? 'Ym'));
    }

    public function disk(): string
    {
        return $this->disk;
    }

    /**
     * Build processed root directory for a given uploaded PDF path (e.g., uploads/abc123.pdf)
     * Example output: processed/202512/abc123
     */
    public function rootDir(string $uploadedPdfPath, ?CarbonInterface $now = null): string
    {
        $now = $now ?: now(config('app.timezone'));
        $dateFolder = $now->format($this->dateFolderFormat);
        $tempName = pathinfo($uploadedPdfPath, PATHINFO_FILENAME);
        return rtrim($this->basePath, '/').'/'.$dateFolder.'/'.$tempName;
    }

    public function ensureDir(string $dir): void
    {
        $disk = Storage::disk($this->disk);
        if (! $disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }
    }

    public function pagesDir(string $uploadedPdfPath, ?CarbonInterface $now = null): string
    {
        return $this->rootDir($uploadedPdfPath, $now).'/pages';
    }

    public function documentPdfPath(string $uploadedPdfPath, ?CarbonInterface $now = null): string
    {
        return $this->rootDir($uploadedPdfPath, $now).'/document.pdf';
    }
}
