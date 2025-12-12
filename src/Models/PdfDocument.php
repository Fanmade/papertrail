<?php

namespace Vqs\Papertrail\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @var string $id UUID
 * @var string $name
 * @var string $path
 * @var string $mime
 * @var int $pages
 * @var int $size
 */
class PdfDocument extends Model
{
    use HasUuids;

    protected $guarded = [];

    public $incrementing = false;

    /**
     * Get all the fields for the PdfDocument
     *
     * @return HasMany<PdfField>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(PdfField::class, 'document_id', 'id');
    }

    /**
     * Get all per-page records for this document.
     *
     * @return HasMany<PdfPage>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(PdfPage::class, 'document_id', 'id')->orderBy('page_number');
    }
}
