<?php

namespace Fanmade\Papertrail\Types;

enum PdfStatus: string
{
    case UPLOADED = 'uploaded';
    case PROCESSED = 'processed';
    case ERROR = 'error';

    public function isError(): bool
    {
        return $this === self::ERROR;
    }

    public function isProcessed(): bool
    {
        return $this === self::PROCESSED;
    }

    public function isUploaded(): bool
    {
        return $this === self::UPLOADED;
    }
}
