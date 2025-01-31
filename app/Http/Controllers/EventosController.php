<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Eventos;

class EventosController extends Controller
{
    public function eventos ()
    {
        return view('eventos.eventosregistro');
    }
    public function listarEventos()
    {
        $eventos = Eventos::orderBy('created_at', 'desc')->get(['id', 'accion', 'descripcion', 'codigo', 'created_at']);
        return response()->json([
            'message' => 'Lista de eventos obtenida exitosamente.',
            'data' => $eventos,
        ], 200);
    }

    /**
     * Buscar eventos por código, ordenados del más nuevo al más antiguo.
     */
    public function buscarPorCodigo($codigo)
    {
        $eventos = Eventos::where('codigo', $codigo)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'accion', 'descripcion', 'codigo', 'created_at']);

        if ($eventos->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron eventos para el código proporcionado.',
            ], 404);
        }

        return response()->json([
            'message' => 'Eventos encontrados.',
            'data' => $eventos,
        ], 200);
    }
    
}