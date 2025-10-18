<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Arbol extends Model
{
  protected $table = 'arboles';
    protected $primaryKey = 'id_arbol';
    protected $fillable = [
        'altura_total',
        'diametro_pecho',
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
    return $this->hasOne(Estimacion1::class, 'id_arbol');
}
    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'id_parcela');
    }

   
    public $incrementing = true;

    // Configuración adicional
    public $timestamps = true; // Si no usas created_at/updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];}