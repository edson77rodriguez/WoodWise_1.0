<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mosaic extends Model
{
    protected $fillable = [

        'uuid',
        'project_id',

        'original_name',
        'object_key',
        'size_bytes',
        'mime_type',
        'extension',

        'checksum_sha256',

        'width',
        'height',
        'bands',
        'dtype',
        'crs',

        'pixel_size_x',
        'pixel_size_y',
        'gsd_cm',

        'bounds',
        'transform',
        'nodata',

        'preview_object_key',

        'status',
        'metadata',

        'uploaded_at',
        'inspected_at',
    ];


    protected $casts = [

        'bounds' =>
            'array',

        'transform' =>
            'array',

        'metadata' =>
            'array',

        'uploaded_at' =>
            'datetime',

        'inspected_at' =>
            'datetime',
    ];


    public function project(): BelongsTo
    {
        return $this->belongsTo(
            Project::class
        );
    }


    public function analysisJobs(): HasMany
    {
        return $this->hasMany(
            AnalysisJob::class
        );
    }
}