<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Admision;
use Carbon\Carbon;

class Dashboardgeneral extends Component
{
    public $totalAdmisiones;
    public $totalEntregados;
    public $totalRecaudado;
    public $admisionesHoy;
    public $departamentos = [
        'LA PAZ', 'COCHABAMBA', 'SANTA CRUZ', 'ORURO',
        'POTOSI', 'CHUQUISACA', 'BENI', 'PANDO', 'TARIJA'
    ];
    public $datosPorDepartamento = [];

    public function mount()
    {
        // Datos generales
        $this->totalAdmisiones = Admision::count();
        $this->totalEntregados = Admision::where('estado', 5)->count();
        $this->totalRecaudado = Admision::sum('precio'); // Ajusta si el campo 'precio' varía.
        $this->admisionesHoy = Admision::whereDate('created_at', Carbon::today())->count();

        // Datos por departamento
        foreach ($this->departamentos as $departamento) {
            $this->datosPorDepartamento[$departamento] = [
                'totalAdmisiones' => Admision::where('origen', $departamento)->count(),
                'totalEntregados' => Admision::where('origen', $departamento)->where('estado', 5)->count(),
                'totalRecaudado' => Admision::where('origen', $departamento)->sum('precio'),
                'admisionesHoy' => Admision::where('origen', $departamento)
                    ->whereDate('created_at', Carbon::today())->count()
            ];
        }
    }

    public function render()
    {
        return view('livewire.dashboardgeneral');
    }
}
