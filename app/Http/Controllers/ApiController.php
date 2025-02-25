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
    
        // 3. Determinar la ciudad desde la base de datos
        $ciudad = !empty($admision->reencaminamiento) 
            ? $admision->reencaminamiento 
            : $admision->ciudad; // Asegúrate de que el campo 'ciudad' esté en tu modelo
    
        // 4. Retornar la respuesta en formato JSON
        return response()->json([
            'CODIGO'       => $admision->codigo,
            'destinatario' => $admision->nombre_destinatario,
            'estado'       => $admision->estado,
            'telefono_d'   => $admision->telefono_destinatario,
            'peso'         => $peso,
            'ciudad'       => $ciudad,
        ], 200);
    }
    
    
    public function cambiarEstadoPorCodigoEMS(Request $request)
    {
        // Validar los datos recibidos, incluida la acción
        // $request->validate([
        //     'codigo' => 'required|string|max:255',
        //     'estado' => 'required|integer',
        //     'user_id' => 'required|integer',
        //     'observacion_entrega' => 'nullable|string',
        //     'usercartero' => 'nullable|string|max:255',
        //     'action' => 'required|string|max:255' // Validar que se mande la acción
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
    
        // Registrar el evento con los datos del request
        Eventos::create([
            'accion'      => $request->action, // La acción enviada desde el PUT
            'descripcion' => 'Actualización de estado a ' . $request->estado, // Descripción del cambio de estado
            'codigo'      => $admision->codigo, // Código de la admisión
            // 'cartero_id'  => $admision->user_id, // ID del usuario responsable
            'fecha_hora'  => now(), // Fecha y hora actual
            'user_id'     => $request->user_id, // ID del usuario que realiza la acción
            'observaciones' => $request->observacion_entrega ?? '', // Observaciones adicionales
            'usercartero' => $request->usercartero // Usuario cartero que realiza la acción
        ]);
    
        return response()->json([
            'message' => 'Admisión actualizada y evento registrado correctamente',
            'admision' => $admision
        ], 200);
    }
    


}
