<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function obtenertarifas ()
    {
        return view('admisiones.tarifas');
    }
}
