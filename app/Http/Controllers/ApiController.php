<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admision;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento
use App\Models\Historico; // Asegúrate de importar el modelo Evento
class ApiController extends Controller
{
    public function admisionPorCodigo($codigo)
{
    // 1. Buscar la admisión que coincida con el "codigo"
    $admision = Admision::where('codigo', $codigo)->first();

    if (!$admision) {
        return response()->json(['message' => 'Admisión no encontrada'], 404);
    }

    // 2. Seleccionar el primer peso disponible: peso_ems, luego peso_regional, finalmente peso
    $peso = $admision->peso_ems 
        ?: ($admision->peso_regional ?: $admision->peso);

    // 3. Determinar la "ciudad final" según reencaminamiento o extraer del código
    if (!empty($admision->reencaminamiento)) {
        // Si tiene reencaminamiento, usamos ese
        $ciudad = $admision->reencaminamiento;
    } else {
        // Extraemos la "segunda" ciudad del código (toma 3 caracteres desde posición 7)
        // Ajustar este índice a tu formato real de código
        $ciudad = strtoupper(substr($codigo, 7, 3));
    }

    // 4. Retornar la respuesta en formato JSON
    return response()->json([
        'CODIGO'       => $admision->codigo,               // Renombrado a "CODIGO"
        'destinatario' => $admision->nombre_destinatario,  // Campo "nombre_destinatario" en DB
        'estado'       => $admision->estado,
        'telefono_d'   => $admision->telefono_destinatario,
        'peso'         => $peso,
        'ciudad'       => $ciudad,                         // Ahora solo devuelve el código de ciudad
    ], 200);
}
public function cambiarEstadoPorCodigoEMS(Request $request)
{
    // Validar los datos recibidos
    // $request->validate([
    //     'codigo' => 'required|string|max:255',
    //     'estado' => 'required|integer',
    //     'user_id' => 'required|integer',
    //     'observacion_entrega' => 'nullable|string',
    //     'usercartero' => 'nullable|string|max:255'
    // ]);

    // Buscar la admisión por el código
    $admision = Admision::where('codigo', $request->codigo)->first();

    if (!$admision) {
        return response()->json(['message' => 'Admisión no encontrada'], 404);
    }

    // Actualizar los campos solicitados
    $admision->estado = $request->estado;
    $admision->user_id = $request->user_id;
    $admision->observacion_entrega = $request->observacion_entrega;
    $admision->usercartero = $request->usercartero;
    $admision->save();

    // // Registrar el evento
    // \App\Models\Eventos::create([
    //     'accion' => 'Actualización de Estado',
    //     'descripcion' => $request->descripcion ?? 'Actualización de estado de la admisión',
    //     'codigo' => $admision->codigo,
    //     'user_id' => $request->user_id,
    // ]);

    // // Registrar el histórico del estado
    // Historico::create([
    //     'numero_guia' => $admision->codigo, // Código único de admisión
    //     'fecha_actualizacion' => now(), // Timestamp actual
    //     'id_estado_actualizacion' => $request->estado, // Estado actualizado
    //     'estado_actualizacion' => 'Estado actualizado', // Descripción genérica del estado
    // ]);

    return response()->json([
        'message' => 'Admisión actualizada y evento registrado correctamente',
        'admision' => $admision
    ], 200);
}


}
