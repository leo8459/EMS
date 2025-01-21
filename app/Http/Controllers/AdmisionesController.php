<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admision;

class AdmisionesController extends Controller
{
    public function getAdmisiones ()
    {
        return view('admisiones.iniciar');
    }
    public function inventarioadmision ()
    {
        return view('admisiones.inventario');
    }
    public function recibiradmision ()
    {
        return view('admisiones.recibir');
    }
    public function ems ()
    {
        return view('admisiones.emsinventario');
    }
    public function asignar ()
    {
        return view('admisiones.asignarcartero');
    }
    public function asignarse ()
    {
        return view('admisiones.asignarsecartero');
    }
    public function encamino ()
    {
        return view('admisiones.encaminocartero');
    }
    public function entregar ()
    {
        return view('admisiones.entregarcartero');
    }
    public function regional ()
    {
        return view('admisiones.recibirregional');
    }
    public function encaminocartero ()
    {
        return view('admisiones.encaminocarteroentrega');
    }

    public function entregadosems ()
    {
        return view('admisiones.entregadosemsjota');
    }
    public function admisiones ()
    {
        return view('admisiones.admisionesgeneradas');
    }
    public function ventanilla ()
    {
        return view('admisiones.entregasventanilla');
    }
    public function recibirventanilla ()
    {
        return view('admisiones.inventarioventanilla');
    }
    public function crearoficiales ()
    {
        return view('admisiones.enviosoficiales');
    }
  
    public function verexpedicion ()
    {
        return view('admisiones.expedicion');
    }






    public function getAdmisionesPorManifiesto(Request $request)
    {
        // Validar que se envíe un manifiesto en la solicitud
        $request->validate([
            'manifiesto' => 'required|string',
        ]);
    
        // Obtener el manifiesto desde el request
        $manifiesto = $request->input('manifiesto');
    
        // Buscar admisiones que coincidan con el manifiesto proporcionado
        $admisiones = Admision::where('manifiesto', $manifiesto)
            ->select([
                'codigo',
                \DB::raw("CASE 
                            WHEN reencaminamiento IS NOT NULL AND reencaminamiento != '' 
                            THEN reencaminamiento 
                            ELSE ciudad 
                          END AS ubicacion"),
                \DB::raw("CASE 
                            WHEN peso_regional IS NOT NULL AND peso_regional != 0 
                            THEN peso_regional 
                            WHEN peso_ems IS NOT NULL AND peso_ems != 0 
                            THEN peso_ems 
                            ELSE peso 
                          END AS peso_final"),
                'manifiesto',
            ])
            ->get();
    
        // Verificar si se encontraron registros
        if ($admisiones->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron admisiones con el manifiesto especificado.',
            ], 404);
        }
    
        // Retornar los registros en formato JSON
        return response()->json([
            'message' => 'Admisiones encontradas.',
            'data' => $admisiones,
        ]);
    }
    

    
public function entregarenvios($id)
{
    // Buscar la admisión por ID
    $admision = \App\Models\Admision::findOrFail($id); // Usa el modelo adecuado

    // Pasar la admisión a la vista
    return view('admisiones.entregarenviosfirma', compact('admision'));
}

    
}