<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Troza extends Model
{
    protected $table = 'trozas';
    protected $primaryKey = 'id_troza';
    protected $fillable = [
        'longitud',
        'diametro',
        'diametro_otro_extremo', // Campo opcional
        'diametro_medio',       // Campo opcional
        'densidad',
        'id_especie',
        'id_parcela',
    ];

    // Relaciones
    public function especie()
    {
        return $this->belongsTo(Especie::class, 'id_especie');
    }
 public function estimacion()
{
    return $this->hasOne(Estimacion::class, 'id_troza');
}

    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'id_parcela');
    }

    // Métodos de acceso (opcionales)
    public function getDiametroCmAttribute()
    {
        return $this->diametro * 100; // Convierte metros a cm
    }

    public function getLongitudCmAttribute()
    {
        return $this->longitud * 100; // Convierte metros a cm
    }
    public $incrementing = true;

    // Configuración adicional
    public $timestamps = true; // Si no usas created_at/updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];}