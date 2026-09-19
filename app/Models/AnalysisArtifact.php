<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisArtifact extends Model
{
    protected $fillable = [

        'uuid',
        'analysis_job_id',

        'type',

        'object_key',
        'filename',
        'mime_type',
        'size_bytes',

        'checksum_sha256',

        'metadata',
    ];


    protected $casts = [

        'metadata' =>
            'array',
    ];


    public function analysisJob(): BelongsTo
    {
        return $this->belongsTo(
            AnalysisJob::class
        );
    }
}