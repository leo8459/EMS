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
}