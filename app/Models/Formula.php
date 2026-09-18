<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formula extends Model
{
    use HasFactory;

    // Definir el nombre de la tabla (si no es el plural de la clase)
    protected $table = 'formulas';

    // Definir la clave primaria si no es 'id'
    protected $primaryKey = 'id_formula';

    // Definir los campos que pueden ser asignados masivamente
    protected $fillable = [
        'nom_formula',
        'expresion',
        'id_tipo_e',
        'id_cat',
        'modo_ejecucion',
        'estado_revision',
        'variables_schema',
        'especies_relacionadas',
        'resultado_tipo',
        'biomasa_factor',
        'carbono_factor',
        'revision_notas',
        'revision_at',

    ];
    protected $casts = [
        'variables_schema' => 'array',
        'especies_relacionadas' => 'array',
        'biomasa_factor' => 'decimal:6',
        'carbono_factor' => 'decimal:6',
        'revision_at' => 'datetime',
    ];
   public function tipoEstimacion()
    {
        return $this->belongsTo(Tipo_Estimacion::class, 'id_tipo_e');
    }
    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'id_cat');
    }
    public function getFormattedCreatedAtAttribute()
{
    return $this->created_at ? $this->created_at->format('d/m/Y') : null;
}

    // Si la clave primaria no es auto-incrementable
    public $incrementing = true;

    // Si no estás utilizando las marcas de tiempo (created_at y updated_at)
    public $timestamps = true; // Si no usas created_at/updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];}
