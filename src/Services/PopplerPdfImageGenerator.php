<?php

namespace Vqs\Papertrail\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;
use Storage;
use Vqs\Papertrail\Contracts\PdfImageGenerator;

use function pathinfo;

class PopplerPdfImageGenerator implements PdfImageGenerator
{
    /**
     * {@inheritDoc}
     */
    public function generateThumbnail(string $pdfAbsolutePath, ?string $targetFilename = null, array $options = []): string
    {
        if ($targetFilename === null) {
            $targetFilename = pathinfo($pdfAbsolutePath, PATHINFO_FILENAME) . '_tumb.png';
        }
        $config = config('papertrail.thumb_defaults');
        $disk = Storage::disk(config('papertrail.thumb_disk'));
        $dir = trim(config('papertrail.thumb_path'), '/');

        $format = strtolower(Arr::get($options, 'format', $config['format'])) === 'jpg' ? 'jpg' : 'png';
        $width = (int) Arr::get($options, 'width', $config['width']);
        $height = (int) Arr::get($options, 'height', $config['height']);
        $density = (int) Arr::get($options, 'density', $config['density']);

        // Render first page to a temp file
        $tmpBase = sys_get_temp_dir() . '/' . uniqid('pdfthumb_', true);
        $cmd = [
            'pdftoppm',
            '-f', '1',
            '-l', '1',
            '-r', (string) $density,
            $pdfAbsolutePath,
            $tmpBase,
        ];
        $process = Process::run($cmd);
        if (! $process->successful()) {
            throw new \RuntimeException('pdftoppm failed: ' . $process->errorOutput());
        }

        $rendered = $tmpBase . '-1.png';
        if (! is_file($rendered)) {
            $rendered = $tmpBase . '-1.ppm';
        }

        // Use GD or Imagick to resize and convert
        $image = imagecreatefromstring(file_get_contents($rendered));
        [$srcW, $srcH] = [imagesx($image), imagesy($image)];
        $ratio = min($width / $srcW, $height / $srcH);
        $dstW = (int) floor($srcW * $ratio);
        $dstH = (int) floor($srcH * $ratio);
        $thumb = imagecreatetruecolor($dstW, $dstH);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        $path = trim(config('papertrail.thumb_path'), '/') . '/' . pathinfo($targetFilename, PATHINFO_FILENAME) . '.' . $format;
        ob_start();
        if ($format === 'jpg') {
            imagejpeg($thumb, null, (int) $config['quality']);
        } else {
            imagepng($thumb);
        }
        $bin = ob_get_clean();
        Storage::disk(config('papertrail.thumb_disk'))->put($path, $bin);

        imagedestroy($thumb);
        imagedestroy($image);

        return $path;
    }
}
