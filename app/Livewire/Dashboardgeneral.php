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
    public $estado7Data = [];
    private $estadoComparativo = [];
    public $estado5Data = [];



    
    public function mount()
    {
        $user = Auth::user();

        if ($user->hasRole('ADMINISTRADOR')) {
            // Mostrar todos los datos a los administradores
            $this->cargarDatosGenerales();
            $this->cargarDatosPorDepartamentos($this->departamentos);
            $this->cargarEstado7Data($this->departamentos); // Llama al nuevo método
            $this->cargarEstado5Data($this->departamentos); // Nuevo método
            $this->cargarEstadoComparativo(); // Nuevo método


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
            'estado7Data' => $this->estado7Data,
            'estado5Data' => $this->estado5Data,
            'estadoComparativo' => $this->estadoComparativo, // Enviar los datos comparativos

        ]);
    }
    
    
    private function cargarEstado7Data(array $departamentos)
{
    $this->estado7Data = [];
    foreach ($departamentos as $departamento) {
        $this->estado7Data[$departamento] = Admision::where('origen', $departamento)->where('estado', 7)->count();
    }
}

private function cargarEstado5Data(array $departamentos)
{
    $this->estado5Data = [];
    foreach ($departamentos as $departamento) {
        $this->estado5Data[$departamento] = Admision::where(function ($query) use ($departamento) {
            $query->where('reencaminamiento', $departamento)
                ->orWhere(function ($q) use ($departamento) {
                    $q->whereNull('reencaminamiento')
                      ->where('ciudad', $departamento);
                });
        })->where('estado', 5)->count();
    }
}
private function cargarEstadoComparativo()
{
    $totalAdmisiones = Admision::count();
    $entregados = Admision::where('estado', 5)->count();
    $noEntregados = $totalAdmisiones - $entregados;

    // Guardar los valores absolutos
    $this->estadoComparativo = [
        'Entregados' => $entregados,
        'No Entregados' => $noEntregados > 0 ? $noEntregados : 0,
    ];
}

}
