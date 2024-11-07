<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admision extends Model
{
    use HasFactory;

    protected $fillable = [
        'origen',
        'fecha',
        'servicio',
        'tipo_correspondencia',
        'cantidad',
        'peso',
        'destino',
        'codigo',
        'precio',
        'numero_factura',
        'nombre_remitente',
        'nombre_envia',
        'carnet',
        'telefono_remitente',
        'nombre_destinatario',
        'telefono_destinatario',
        'direccion',
        'provincia',
        'ciudad',
        'pais',
    ];
}
