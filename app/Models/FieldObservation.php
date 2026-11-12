<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldObservation extends Model
{
    protected $table = 'field_observations';

    protected $fillable = [
        'uuid', 'user_id', 'id_parcela', 'grupo', 'tree_id',
        'candidate_id', 'payload', 'payload_sha256',
        'client_created_at', 'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'client_created_at' => 'datetime',
        'synced_at' => 'datetime',
    ];
}
