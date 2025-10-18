<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especie extends Model
{
    use HasFactory;

    // Definir el nombre de la tabla (si no es el plural de la clase)
    protected $table = 'especies';

    // Definir la clave primaria si no es 'id'
    protected $primaryKey = 'id_especie';

    // Definir los campos que pueden ser asignados masivamente
    protected $fillable = [
        'nom_cientifico',
        'nom_comun',
        'imagen', // Agrega este campo
    ];

    // Si la clave primaria no es auto-incrementable
    public $incrementing = true;

    // Si no estás utilizando las marcas de tiempo (created_at y updated_at)
    public $timestamps = true; // Si no usas created_at/updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];}
