<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    public function dashboardadmin ()
    {
        return view('admisiones.dashboardgeneral');
    }
}
