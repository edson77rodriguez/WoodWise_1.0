<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_persona',
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $dates = [
        'created_at',
        'updated_at'
    ];
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function getRolIdAttribute()
    {
        return $this->persona ? $this->persona->id_rol : null;
    }
    public $timestamps = true; // Si no usas created_at/updated_at
    public $incrementing = true;

    // En app/Models/User.php

public function productor()
{
    // Asume que la tabla 'productores' tiene una columna 'id_persona' que es la clave foránea.
    // Y que el id del User es el mismo que el id_persona.
    return $this->hasOne(Productor::class, 'id_persona', 'id');
}
}

