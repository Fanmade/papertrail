<?php

namespace Vqs\Papertrail\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $document_id
 * @property int $page_number
 * @property int $width_pt
 * @property int $height_pt
 * @property int $width_px
 * @property int $height_px
 * @property int $dpi
 * @property string $image_path
 */
class PdfPage extends Model
{
    use HasUuids;

    protected $guarded = [];

    public $incrementing = false;

    public function document(): BelongsTo
    {
        return $this->belongsTo(PdfDocument::class, 'document_id', 'id');
    }
}
