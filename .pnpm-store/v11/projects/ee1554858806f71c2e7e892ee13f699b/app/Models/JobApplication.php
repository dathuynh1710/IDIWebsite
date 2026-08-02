<?php

namespace App\Models;

use App\Enums\JobApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'job_position_id', 'full_name', 'email', 'phone', 'address',
        'cover_letter', 'cv_media_id', 'status', 'internal_note',
        'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobApplicationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id');
    }

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cv_media_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
