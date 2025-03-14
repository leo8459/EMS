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
        // 1. Buscar la admisión
        $admision = Admision::where('codigo', $request->codigo)->first();
    
        if (!$admision) {
            return response()->json(['message' => 'Admisión no encontrada'], 404);
        }
    
        // 2. Determinamos el user_id en base a usercartero
        $carteroId = null;
        if (!empty($request->usercartero)) {
            $userMatch = User::where('name', $request->usercartero)->first();
            if ($userMatch) {
                $carteroId = $userMatch->id;
            }
        }
    
        // 3. Asignar la descripción del estado
        $estadoDescripcion = match ($request->estado) {
            4  => 'Envío en camino',
            5  => 'Envío entregado',
            10 => 'Retorno',
            default => 'En inventario'
        };
    
        // 4. Actualizar los campos en la admisión
        $admision->estado              = $request->estado;
        $admision->user_id             = $carteroId;
        $admision->observacion_entrega = $request->observacion_entrega;
        $admision->usercartero         = $request->usercartero;
        $admision->save();
    
        // 5. Registrar el evento con la nueva descripción
        Eventos::create([
            'accion'        => $request->action,
            'descripcion'   => 'Actualización de estado: ' . $estadoDescripcion,
            'codigo'        => $admision->codigo,
            'fecha_hora'    => now(),
            'user_id'       => $carteroId,
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
        // 1. Buscar la admisión por "codigo"
        $admision = Admision::where('codigo', $request->codigo)->first();
    
        // Si no se encuentra, retornar error
        if (!$admision) {
            return response()->json(['message' => 'Admisión no encontrada'], 404);
        }
    
        // 2. Actualizar la admisión
        //    Guardamos 'photo' tal y como llega (con la cadena base64)
        $admision->update([
            'estado'              => $request->estado,
            'firma_entrega'       => $request->firma_entrega,
            'observacion_entrega' => $request->observacion_entrega,
            'photo'               => $request->photo,
        ]);
    
        // // 3. Registrar evento en la tabla "eventos"
        // Eventos::create([
        //     'accion'        => 'Entrega realizada',
        //     'descripcion'   => 'Se entregó el envío con código ' . $admision->codigo,
        //     'codigo'        => $admision->codigo,
        //     'fecha_hora'    => now(),
        //     'user_id'       => Auth::id(),  // <-- asumiendo que hay un usuario autenticado
        //     'observaciones' => $request->observacion_entrega ?? '',
        //     'usercartero'   => Auth::user()->name ?? 'Desconocido',
        // ]);
    
        // 4. Retornar la respuesta JSON
        return response()->json([
            'message'  => 'Envío entregado con éxito',
            'admision' => $admision
        ], 200);
    }
    
    

    
    


}
