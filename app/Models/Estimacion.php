<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estimacion extends Model
{
    use HasFactory;

    protected $table = 'estimaciones';
    protected $primaryKey = 'id_estimacion';
    protected $fillable = [
        'id_tipo_e',
        'id_formula',
        'calculo',
        'biomasa',
        'carbono',
        'id_troza',
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

   public function troza()
{
    return $this->belongsTo(Troza::class, 'id_troza');
}

    public $incrementing = true;
    public $timestamps = true; // Si no usas created_at/updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];}