<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldMissionManualTarget extends Model
{
    protected $fillable = [
        'uuid', 'field_mission_id', 'created_by', 'updated_by',
        'geometry_type', 'geometry', 'crs', 'probable_species',
        'reason', 'priority', 'status', 'note',
    ];

    protected $casts = ['geometry' => 'array'];

    public function mission()
    {
        return $this->belongsTo(FieldMission::class, 'field_mission_id');
    }

    public function toMissionFeature(): array
    {
        return [
            'type' => 'Feature',
            'id' => $this->uuid,
            'geometry' => $this->geometry,
            'properties' => [
                'manual_target_uuid' => $this->uuid,
                'manual_code' => sprintf('MAN-%06d', $this->id),
                'source_type' => 'manual_desktop',
                'probable_species' => $this->probable_species,
                'reason' => $this->reason,
                'priority' => $this->priority,
                'note' => $this->note,
                'review_status' => 'pending_field',
            ],
        ];
    }
}
