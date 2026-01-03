<?php

namespace Fanmade\Papertrail\Services;

use Fanmade\Papertrail\Contracts\FormFiller;
use Fanmade\Papertrail\Models\PdfDocument;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\Local\LocalFilesystemAdapter;

use function dirname;
use function json_encode;
use function logger;
use function mkdir;
use function storage_path;
use function tempnam;
use function unlink;
use function sys_get_temp_dir;
use function file_put_contents;
use function stream_copy_to_stream;
use function data_get;
use function config;
use function fclose;
use function fopen;
use function file_exists;

class PythonFormFiller implements FormFiller
{
    public function execute(PdfDocument $document, array $contextData, string $savePath = null): string
    {
        $tempInputPath = null;
        $tempJsonPath = null;

        try {
            // 1. Resolve the Input PDF Path (Handle S3 vs. Local)
            $inputPath = $this->resolveLocalPdfPath($document, $tempInputPath);

            // 2. Prepare Output Path
            $outputPath = $savePath ?? storage_path('app/generated/' . Str::random(16) . '.pdf');

            // 3. Create the output directory if it doesn't exist
            if (!file_exists(dirname($outputPath)) && !mkdir($concurrentDirectory = dirname($outputPath), 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }

            // 4. Prepare Data (Write JSON to file to be safe)
            $fieldData = $this->mapFieldsToData($document, $contextData);
            $tempJsonPath = tempnam(sys_get_temp_dir(), 'pdf_data');
            file_put_contents($tempJsonPath, json_encode($fieldData));

            // 5. Run the Python script
            $scriptPath = __DIR__ . '/../../scripts/fill_form.py';

            $result = Process::run([
                'python3',
                $scriptPath,
                $inputPath,
                $outputPath,
                $tempJsonPath
            ]);

            if ($result->failed()) {
                logger()->error('PDF Generation failed', [
                    'command' => $result->command(),
                    'exitCode' => $result->exitCode(),
                    'errorOutput' => $result->errorOutput(),
                    'output' => $result->output(),
                ]);
                throw new \RuntimeException("PDF Generation failed: " . $result->errorOutput());
            }

            return $outputPath;

        } finally {
            // 5. Cleanup: Always delete temp files, even if the script fails
            if ($tempInputPath && file_exists($tempInputPath)) {
                unlink($tempInputPath);
            }
            if ($tempJsonPath && file_exists($tempJsonPath)) {
                unlink($tempJsonPath);
            }
        }
    }

    /**
     * returns a path to a local file.
     * If the file is on S3, it populates $tempInputPath so it can be deleted later.
     */
    protected function resolveLocalPdfPath(PdfDocument $document, &$tempInputPath): string
    {
        $filePath = $document->path . DIRECTORY_SEPARATOR . 'document.pdf';

        $disk = Storage::disk(config('papertrail.processed.disk', 'papertrail'));

        // Optimization: If the disk is local, just get the path directly
        if ($disk->getAdapter() instanceof LocalFilesystemAdapter) {
            return $disk->path($filePath);
        }

        // Fallback for S3 / Remote: Download to a temp file
        $tempInputPath = tempnam(sys_get_temp_dir(), 'pdf_template');

        // Stream the file to save memory (better than file_get_contents)
        $handle = fopen($tempInputPath, 'wb');
        $stream = $disk->readStream($document->path);
        stream_copy_to_stream($stream, $handle);
        fclose($handle);

        return $tempInputPath;
    }

    protected function mapFieldsToData(PdfDocument $document, array $contextData): array
    {
        $mappedData = [];
        $fields = $document->fields()->whereNotNull('assigned_placeholder')->get();

        /** @var \Fanmade\Papertrail\Models\PdfField $field */
        foreach ($fields as $field) {
            $mappedData[$field->name] = data_get($contextData, $field->assigned_placeholder);
        }

        return $mappedData;
    }
}