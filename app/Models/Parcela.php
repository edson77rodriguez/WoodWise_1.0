<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    use HasFactory;

    // Definir el nombre de la tabla (si no es el plural de la clase)
    protected $table = 'parcelas';

    // Definir la clave primaria si no es 'id'
    protected $primaryKey = 'id_parcela';

    // Definir los campos que pueden ser asignados masivamente
    protected $fillable = [
        'nom_parcela',
        'ubicacion',
        'id_productor',
        'extension',
        'direccion',
        'CP',
    ];
public function turnosCorta()
{
    return $this->hasMany(Turno_Corta::class, 'id_parcela');
}
public function estimacion()
{
    return $this->hasOne(Estimacion::class, 'id_parcela');
}
    // Relación con la tabla 'productores'
    public function productor()
    {
        return $this->belongsTo(Productor::class, 'id_productor');
    }
    public function tecnicos()
    {
        return $this->belongsToMany(Tecnico::class, 'asigna_parcelas', 'id_parcela', 'id_tecnico');
    }

    // Relación con asignaciones (tabla asigna_parcelas)
    public function asignaciones()
    {
        return $this->hasMany(Asigna_Parcela::class, 'id_parcela');
    }


 
// En Parcela.php
public function estimaciones()
{
    return $this->hasManyThrough(
        Estimacion::class,
        Troza::class,
        'id_parcela', // Foreign key on trozas table
        'id_troza',   // Foreign key on estimaciones table
        'id_parcela', // Local key on parcelas table
        'id_troza'    // Local key on trozas table
    );
}
public function arboles()
    {
        return $this->hasMany(Arbol::class, 'id_parcela', 'id_parcela');
    }
    
public function trozas()
{
    return $this->hasMany(Troza::class, 'id_parcela', 'id_parcela');
}
    // Si la clave primaria no es auto-incrementable
    public $incrementing = true;

    // Si no estás utilizando las marcas de tiempo (created_at y updated_at)
    public $timestamps = true; // Si no usas created_at/updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];}
