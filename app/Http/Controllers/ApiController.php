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
        // (Opcional) Validaciones de los campos que tu endpoint requiere
        // $request->validate([
        //     'codigo' => 'required|string|max:255',
        //     'estado' => 'required|integer',
        //     'observacion_entrega' => 'nullable|string',
        //     'usercartero' => 'nullable|string|max:255',
        //     'action' => 'required|string|max:255'
        // ]);
    
        // 1. Buscar la admisión
        $admision = Admision::where('codigo', $request->codigo)->first();
    
        if (!$admision) {
            return response()->json(['message' => 'Admisión no encontrada'], 404);
        }
    
        // 2. Determinamos el user_id en base a usercartero
        //    Buscamos en la tabla "users" quien tenga name = usercartero
        $carteroId = null;
        if (!empty($request->usercartero)) {
            // Si el cartero existe
            $userMatch = User::where('name', $request->usercartero)->first();
            if ($userMatch) {
                // Asignamos su id a la admisión
                $carteroId = $userMatch->id;
            }
        }
    
        // 3. Actualizar los campos en la admisión
        $admision->estado              = $request->estado;
        $admision->user_id             = $carteroId; // user_id se establece con el ID encontrado (si existe)
        $admision->observacion_entrega = $request->observacion_entrega;
        $admision->usercartero         = $request->usercartero;
        $admision->save();
    
        // 4. Registrar el evento
        Eventos::create([
            'accion'        => $request->action,
            'descripcion'   => 'Actualización de estado a ' . $request->estado,
            'codigo'        => $admision->codigo,  // Código de la admisión
            // 'cartero_id'    => $carteroId,         // ID del usercartero (si se encontró)
            'fecha_hora'    => now(),
            'user_id'       => $carteroId,         // ID del usuario también para el campo user_id
            'observaciones' => $request->observacion_entrega ?? '',
            'usercartero'   => $request->usercartero
        ]);
    
        return response()->json([
            'message'  => 'Admisión actualizada y evento registrado correctamente',
            'admision' => $admision
        ], 200);
    }
    
    public function entregarEnvio(Request $request)
    {
        // Validar los datos de entrada
        // $request->validate([
        //     'codigo'              => 'required|string|exists:admisions,codigo', // Se asegura de que el código exista
        //     'estado'              => 'required|integer',
        //     'firma_entrega'       => 'required|string',
        //     'observacion_entrega' => 'nullable|string',
        // ]);
    
        // Buscar la admisión por código (clave única)
        $admision = Admision::where('codigo', $request->codigo)->first();
    
        if (!$admision) {
            return response()->json(['message' => 'Admisión no encontrada'], 404);
        }
    
        // Realizar la actualización solo si el código existe
        $admision->update([
            'estado'              => $request->estado,
            'firma_entrega'       => $request->firma_entrega,
            'observacion_entrega' => $request->observacion_entrega,
            'photo'                => $request->photo  // <-- ajusta esto si tu request se llama $request->phot
        ]);
        
    
        // Registrar evento en la tabla eventos
        Eventos::create([
            'accion'        => 'Entrega realizada',
            'descripcion'   => 'Se entregó el envío con código ' . $admision->codigo,
            'codigo'        => $admision->codigo,
            'fecha_hora'    => now(),
            'user_id'       => Auth::id(),
            'observaciones' => $request->observacion_entrega ?? '',
            'usercartero'   => Auth::user()->name ?? 'Desconocido',
        ]);
    
        return response()->json([
            'message'  => 'Envío entregado con éxito',
            'admision' => $admision
        ], 200);
    }
    

    
    


}
