<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estimacion1 extends Model
{
    use HasFactory;

    protected $table = 'estimaciones1';
    protected $primaryKey = 'id_estimacion1';
    protected $fillable = [
        'id_tipo_e',
        'id_formula',
        'calculo',
        'id_arbol',
    ];

    // Relaciones
    public function tipoEstimacion()
    {
        return $this->belongsTo(Tipo_Estimacion::class, 'id_tipo_e');
    }

    public function formula()
    {
        return $this->belongsTo(Formula::class, 'id_formula');
    }

   public function arbol()
{
    return $this->belongsTo(Arbol::class, 'id_arbol');
}
protected $dateFormat = 'Y-m-d H:i:s'; // Formato para todas las fechas

// O para serialización a JSON
protected function serializeDate(\DateTimeInterface $date)
{
    return $date->format('Y-m-d H:i:s');
}
    public $incrementing = true;
    public $timestamps = true; // Si no usas created_at/updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];}