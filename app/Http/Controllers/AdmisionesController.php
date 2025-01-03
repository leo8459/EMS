<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    public function entregarenvios($id)
    {
        // Buscar la admisión por ID
        $admision = \App\Models\Admision::findOrFail($id); // Usa el modelo adecuado
    
        // Pasar la admisión a la vista
        return view('admisiones.entregarenviosfirma', compact('admision'));
    }
    
    
}