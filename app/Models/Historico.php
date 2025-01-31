<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historico extends Model
{
    use HasFactory;
    protected $table = 'historico';

    protected $fillable = [
        'numero_guia',
        'fecha_hora_admision',
        'fecha_actualizacion',
        'id_estado_actualizacion',
        'estado_actualizacion',
    ];
}
