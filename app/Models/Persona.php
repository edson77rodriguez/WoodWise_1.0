<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';
    protected $primaryKey = 'id_persona';

    protected $fillable = ['nom', 'ap', 'am', 'telefono', 'correo', 'contrasena', 'id_rol'];

    public function user()
    {
        return $this->hasOne(User::class, 'id_persona');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
    public $timestamps = true; // Si no usas created_at/updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];
    public $incrementing = true;
 public function tecnico()
    {
        return $this->hasOne(Tecnico::class, 'id_persona');
    }
    public function productor()
{
    return $this->hasOne(Productor::class, 'id_persona');
}
}

