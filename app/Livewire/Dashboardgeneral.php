<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();

        if ($user->hasRole('ADMINISTRADOR')) {
            // Mostrar todos los datos a los administradores
            $this->cargarDatosGenerales();
            $this->cargarDatosPorDepartamentos($this->departamentos);
        } elseif ($user->hasRole('ADMISION')) {
            // Mostrar datos solo para el departamento asignado al usuario
            $departamentoUsuario = $user->city; // Asegúrate de que el modelo de usuario tenga este atributo
            if (in_array($departamentoUsuario, $this->departamentos)) {
                $this->cargarDatosGenerales($departamentoUsuario);
                $this->cargarDatosPorDepartamentos([$departamentoUsuario]);
            } else {
                $this->datosPorDepartamento = [];
            }
        } elseif ($user->hasRole('EMS')) {
            // Mostrar datos específicos para usuarios con el rol 'EMS'
            $this->cargarDatosGenerales();
            $departamentoUsuario = $user->city ?? null;
            if ($departamentoUsuario) {
                $this->cargarDatosPorDepartamentos([$departamentoUsuario]);
            }
        } elseif ($user->hasRole('CARTERO')) {
            // Mostrar datos específicos para usuarios con el rol 'CARTERO'
            $departamentoUsuario = $user->city ?? null;
            if ($departamentoUsuario && in_array($departamentoUsuario, $this->departamentos)) {
                $this->cargarDatosGenerales($departamentoUsuario);
                $this->cargarDatosPorDepartamentos([$departamentoUsuario]);
            } else {
                $this->datosPorDepartamento = [];
            }
        }
    }

    private function cargarDatosGenerales($departamento = null)
    {
        if ($departamento) {
            $this->totalAdmisiones = Admision::where('origen', $departamento)->count();
            $this->totalEntregados = Admision::where('origen', $departamento)->where('estado', 5)->count();
            $this->totalRecaudado = Admision::where('origen', $departamento)->sum('precio');
            $this->admisionesHoy = Admision::where('origen', $departamento)->whereDate('created_at', Carbon::today())->count();
        } else {
            $this->totalAdmisiones = Admision::count();
            $this->totalEntregados = Admision::where('estado', 5)->count();
            $this->totalRecaudado = Admision::sum('precio');
            $this->admisionesHoy = Admision::whereDate('created_at', Carbon::today())->count();
        }
    }

    private function cargarDatosPorDepartamentos(array $departamentos)
    {
        foreach ($departamentos as $departamento) {
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
        return view('livewire.dashboardgeneral', [
            'totalAdmisiones' => $this->totalAdmisiones,
            'totalEntregados' => $this->totalEntregados,
            'totalRecaudado' => $this->totalRecaudado,
            'admisionesHoy' => $this->admisionesHoy,
            'datosPorDepartamento' => $this->datosPorDepartamento,
        ]);
    }
}
