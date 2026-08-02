<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class InvestorDocumentFile extends Model
{
    use HasTranslations;

    public array $translatable = ['display_name'];

    protected $fillable = [
        'investor_document_id', 'media_id', 'locale', 'display_name', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['display_name' => 'array'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(InvestorDocument::class, 'investor_document_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
