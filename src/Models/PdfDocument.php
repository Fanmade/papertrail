<?php

namespace Fanmade\Papertrail\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id UUID
 * @property string $name
 * @property string $path
 * @property string $mime
 * @property int $pages
 * @property int $size
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 */
class PdfDocument extends Model
{
    use HasUuids;
    protected $guarded = [];

    public $incrementing = false;

    public function getFullPath(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'document.pdf';
    }

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
