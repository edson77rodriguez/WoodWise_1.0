<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asigna_Parcela extends Model
{
    use HasFactory;

    // Definir el nombre de la tabla (si no es el plural de la clase)
    protected $table = 'asigna_parcelas';

    // Definir la clave primaria si no es 'id'
    protected $primaryKey = 'id_asigna_p';

    // Definir los campos que pueden ser asignados masivamente
    protected $fillable = [
        'id_tecnico',
        'id_parcela',
    ];

  
    // Relación con la tabla 'tecnicos'
    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'id_tecnico');
    }

    // Relación con la tabla 'parcelas'
    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'id_parcela');
    }

    // Si la clave primaria no es auto-incrementable
    public $incrementing = true;

    // Si no estás utilizando las marcas de tiempo (created_at y updated_at)
    public $timestamps = true; // Si no usas created_at/updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];}
