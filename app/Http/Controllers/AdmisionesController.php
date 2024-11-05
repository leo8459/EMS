<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdmisionesController extends Controller
{
    public function getAdmisiones ()
    {
        return view('admisiones.iniciar');
    }
}