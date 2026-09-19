<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'description',
        'target_species',
        'companion_species',
        'status',
        'settings',
    ];


    protected $casts = [
        'settings' => 'array',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function mosaics(): HasMany
    {
        return $this->hasMany(
            Mosaic::class
        );
    }
}