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
}