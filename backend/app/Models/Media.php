<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'disk',
        'directory',
        'file_name',
        'external_url',
        'original_name',
        'mime_type',
        'extension',
        'file_size',
        'width',
        'height',
        'title',
        'alt_text',
        'caption',
        'created_by',
    ];

    protected function casts(): array
    {
        return ['title' => 'array', 'alt_text' => 'array', 'caption' => 'array'];
    }

    public function getUrlAttribute(): string
    {
        if ($this->external_url) {
            return $this->external_url;
        }

        return Storage::disk($this->disk)->url(trim($this->directory.'/'.$this->file_name, '/'));
    }
}
