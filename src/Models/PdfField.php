<?php

namespace Fanmade\Papertrail\Models;

use Illuminate\Database\Eloquent\Model;
use Fanmade\Papertrail\Types\PdfFormField;

/**
 * @property int $id
 * @property string $name
 * @property string|null $value
 * @property string $document_id
 * @property string|null $type
 * @property int $page_number
 * @property float $x
 * @property float $y
 * @property float $width
 * @property float $height
 * @property string|null $assigned_placeholder
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
