<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admision;

class ApiController extends Controller
{
    public function admisionPorCodigo($codigo)
{
    // 1. Buscar la admisión que coincida con el "codigo"
    $admision = Admision::where('codigo', $codigo)->first();

    if (! $admision) {
        return response()->json(['message' => 'Admisión no encontrada'], 404);
    }

    // 2. Seleccionar el primer peso disponible: peso_ems, luego peso_regional, finalmente peso
    $peso = $admision->peso_ems 
        ?: ($admision->peso_regional ?: $admision->peso);

    // 3. Definir un mapeo de los códigos de ciudad a su nombre
    $ciudadesMap = [
        'LPB' => 'La Paz (LPB)',
        'SRZ' => 'Santa Cruz (SRZ)',
        'CBB' => 'Cochabamba (CBB)',
        'ORU' => 'Oruro (ORU)',
        'PTI' => 'Potosí (PTI)',
        'TJA' => 'Tarija (TJA)',
        'SRE' => 'Sucre (SRE)',
        'BEN' => 'Trinidad (TDD)',
        'CIJ' => 'Cobija (CIJ)',
    ];

    // 4. Determinar la "ciudad final" según reencaminamiento o extraer del código
    if (! empty($admision->reencaminamiento)) {
        // Si tiene reencaminamiento, usamos ese
        $codigoCiudad = strtoupper($admision->reencaminamiento);
    } else {
        // Extraemos la "segunda" ciudad del código (toma 3 caracteres desde posición 7)
        // Ajustar este índice a tu formato real de código
        $codigoCiudad = substr($codigo, 7, 3);
        $codigoCiudad = strtoupper($codigoCiudad);
    }

    // Convertir el código a nombre de ciudad
    $nombreCiudad = $ciudadesMap[$codigoCiudad] ?? 'Desconocida';

    // 5. Retornar la respuesta en formato JSON
    return response()->json([
        'CODIGO'       => $admision->codigo,               // Renombrado a "CODIGO"
        'destinatario' => $admision->nombre_destinatario,  // Campo "nombre_destinatario" en DB
        'estado'       => $admision->estado,
        'telefono_d'   => $admision->telefono_destinatario,
        'peso'         => $peso,
        'ciudad'       => $nombreCiudad,
    ], 200);
}
}
