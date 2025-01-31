<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Historico;

class HistoricoController extends Controller
{
    public function historicos ()
    {
        return view('eventos.historicoatt');
    }
    public function buscarPorNumeroGuia($numero_guia)
{
    // Buscar todos los registros con el número de guía, ordenados por fecha de actualización (descendente)
    $historico = Historico::where('numero_guia', $numero_guia)
        ->orderBy('fecha_actualizacion', 'desc')
        ->get(['numero_guia', 'created_at', 'fecha_actualizacion', 'id_estado_actualizacion', 'estado_actualizacion']);

    // Verificar si existen registros
    if ($historico->isEmpty()) {
        return response()->json([
            'message' => 'No se encontraron registros para el número de guía proporcionado.',
        ], 404);
    }

    // Obtener el registro más reciente
    $registroMasReciente = $historico->first();

    // Excluir el registro más reciente de la lista de "demás registros"
    $otrosRegistros = $historico->slice(1);

    // Devolver los datos en el formato requerido
    return response()->json([
        'message' => 'Registros encontrados.',
        'numero_guia' => $numero_guia, // Mostrar el número de guía
        'registro' => [
            'numero_guia' => $registroMasReciente->numero_guia,
            'fecha_hora_admision' => $registroMasReciente->created_at, // Usar el valor de 'created_at'
            'fecha_actualizacion' => $registroMasReciente->fecha_actualizacion,
            'id_estado_actualizacion' => $registroMasReciente->id_estado_actualizacion,
            'estado_actualizacion' => $registroMasReciente->estado_actualizacion,
        ],
        'historico' => $otrosRegistros,
    ], 200);
}


    
    
}
