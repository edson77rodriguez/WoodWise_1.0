<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotSesion extends Model
{
    use HasFactory;

    protected $table = 'bot_sesiones';

    protected $fillable = [
        'telefono',
        'estado',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
