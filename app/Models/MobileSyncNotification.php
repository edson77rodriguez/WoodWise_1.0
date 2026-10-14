<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileSyncNotification extends Model
{
    protected $table = 'mobile_sync_notifications';

    protected $fillable = [
        'sync_id',
        'id_tecnico',
        'id_productor',
        'payload',
        'payload_hash',
        'status',
        'attempts',
        'notified_at',
        'last_error',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'notified_at' => 'datetime',
    ];
}