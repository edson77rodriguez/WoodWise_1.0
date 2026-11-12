<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldMission extends Model
{
    protected $fillable = [
        'uuid', 'created_by', 'id_parcela', 'analysis_job_id', 'title',
        'status', 'crs', 'bounds', 'geojson', 'selected_count',
    ];

    protected $casts = ['bounds' => 'array', 'geojson' => 'array'];

    public function analysisJob()
    {
        return $this->belongsTo(AnalysisJob::class, 'analysis_job_id');
    }
}
