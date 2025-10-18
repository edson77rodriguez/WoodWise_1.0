<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Tipo_Estimacion extends Model
{
    use HasFactory;


    
        // Definir el nombre de la tabla (si no es el plural de la clase)
        protected $table = 'tipo_estimaciones';
    
        // Definir la clave primaria si no es 'id'
        protected $primaryKey = 'id_tipo_e';
    
        // Definir los campos que pueden ser asignados masivamente
        protected $fillable = [
            'desc_estimacion',
        ];
    
    
        // Si la clave primaria no es auto-incrementable
        public $incrementing = true;
    
        // Si no estás utilizando las marcas de tiempo (created_at y updated_at)
        public $timestamps = true; // Si no usas created_at/updated_at
        protected $dates = [
            'created_at',
            'updated_at'
        ];    
    
}
