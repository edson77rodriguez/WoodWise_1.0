<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioMercado extends Model
{
    use HasFactory;

    protected $table = 'precios_mercado';

    protected $fillable = [
        'especie',
        'estado',
        'precio_por_m3',
        'moneda',
        'fuente',
        'ultima_actualizacion',
    ];

    protected $casts = [
        'precio_por_m3' => 'decimal:2',
        'ultima_actualizacion' => 'date',
    ];
}