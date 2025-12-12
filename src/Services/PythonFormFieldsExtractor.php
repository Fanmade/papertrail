<?php

namespace Vqs\Papertrail\Services;

use Illuminate\Support\Facades\Process;
use Vqs\Papertrail\Contracts\PdfFormFieldExtractor;
use Vqs\Papertrail\Types\PdfFormField;

use function json_decode;

class PythonFormFieldsExtractor implements PdfFormFieldExtractor
{
    /**
     * {@inheritDoc}
     */
    public function extractFields(string $pdfAbsolutePath): array
    {
        $scriptPath = __DIR__ . '/../../scripts/extract_pdf_form_fields.py';

        $result = Process::run(['python3', $scriptPath, $pdfAbsolutePath]);

        $fields = [];
        if ($result->successful()) {
            $fieldsData = json_decode($result->output(), true);
            if (is_array($fieldsData) && ! empty($fieldsData)) {
                foreach ($fieldsData as $field) {
                    if (! is_array($field)) {
                        continue;
                    }
                    $fields[] = PdfFormField::fromArray($field);
                }
            }
        }

        return $fields;
    }
}
