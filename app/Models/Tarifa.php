<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    use HasFactory;
    protected $fillable = [
        'servicio',
        'peso_min',
        'peso_max',
        'ems_local_cobertura_1',
        'ems_local_cobertura_2',
        'ems_local_cobertura_3',
        'ems_local_cobertura_4',
        'ems_nacional',
        'destino_1',
        'destino_2',
        'destino_3',
    ];

    public function admisions()
    {
        return $this->hasMany(Admision::class);
    }
}
