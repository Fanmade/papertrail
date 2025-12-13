<?php

namespace Fanmade\Papertrail\Models;

use Illuminate\Database\Eloquent\Model;
use Fanmade\Papertrail\Types\PdfFormField;

/**
 * @var int $id
 * @var string $name
 * @var string|null $value
 * @var string $document_id
 * @var string|null $type
 * @var int $page_number
 * @var float $x
 * @var float $y
 * @var float $width
 * @var float $height
 * @var string|null $assigned_placeholder
 */
class PdfField extends Model
{
    public $guarded = [];

    public static function fromDto(PdfFormField $dto): static
    {
        return new static([
            'name' => $dto->name,
            'value' => $dto->value,
            'type' => $dto->type,
            'page_number' => $dto->page,
            'x' => $dto->rect->x,
            'y' => $dto->rect->y,
            'width' => $dto->rect->width,
            'height' => $dto->rect->height,
        ]);
    }
}
