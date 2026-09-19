<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisJob extends Model
{
    protected $fillable = [

        'uuid',
        'mosaic_id',

        'analysis_type',
        'status',

        'progress',
        'total_tiles',
        'processed_tiles',

        'models',
        'parameters',
        'summary',

        'result_prefix',

        'error_message',

        'started_at',
        'completed_at',
        'heartbeat_at',
    ];


    protected $casts = [

        'models' =>
            'array',

        'parameters' =>
            'array',

        'summary' =>
            'array',

        'started_at' =>
            'datetime',

        'completed_at' =>
            'datetime',

        'heartbeat_at' =>
            'datetime',
    ];


    public function mosaic(): BelongsTo
    {
        return $this->belongsTo(
            Mosaic::class
        );
    }


    public function artifacts(): HasMany
    {
        return $this->hasMany(
            AnalysisArtifact::class
        );
    }
}