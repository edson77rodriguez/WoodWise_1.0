<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productor extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'productores';

    // Clave primaria personalizada
    protected $primaryKey = 'id_productor';

    // Auto-incremento y timestamps
    public $incrementing = true;
    public $timestamps = true;

    // Campos asignables masivamente
    protected $fillable = [
        'id_persona',
    ];

    // Relación con la tabla 'personas'
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    // Relación con la tabla 'parcelas'
    public function parcelas()
    {
        return $this->hasMany(Parcela::class, 'id_productor');
    }
  public function trozas()
{
    return $this->hasManyThrough(
        Troza::class,
        Parcela::class,
        'id_productor', // Foreign key on parcelas table
        'id_parcela',   // Foreign key on trozas table
        'id_productor', // Local key on productores table
        'id_parcela'    // Local key on parcelas table
    );
}

public function estimaciones()
{
    return $this->hasManyThrough(
        Estimacion::class,
        Parcela::class,
        'id_productor', // Foreign key on parcelas table
        'id_troza',     // Foreign key on estimaciones table
        'id_productor', // Local key on productores table
        'id_parcela'    // Local key on parcelas table
    )->join('trozas', 'estimaciones.id_troza', '=', 'trozas.id_troza');
}
}
