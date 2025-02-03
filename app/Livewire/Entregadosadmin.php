<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use App\Models\User; // Importar el modelo User
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class Entregadosadmin extends Component
{
    use WithPagination;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $selectedCartero = null; // Filtro por cartero
    public $startDate = null; // Fecha de inicio
    public $endDate = null;   // Fecha de fin
    public $department = null; // Filtro opcional por departamento

    public function render()
    {
        // Obtener la ciudad y departamento del usuario autenticado
        $userCity = Auth::user()->city;
        $userDepartment = Auth::user()->department;
    
        // Recuperar solo los carteros que pertenecen a la misma ciudad que el usuario autenticado
        $carteros = User::role('CARTERO') // Filtra usuarios con el rol "CARTERO"
            ->where('city', $userCity) // Filtra por ciudad
            ->get();
    
        // Consulta de admisiones con filtros
        $admisiones = Admision::with('user')
            ->where('estado', 5) // Filtrar solo estado 5
            ->when($this->searchTerm, function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%');
            })
            ->when($this->selectedCartero, function ($query) {
                $query->where('user_id', $this->selectedCartero);
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $start = Carbon::parse($this->startDate)->startOfDay(); // Inicio del día
                $end = Carbon::parse($this->endDate)->endOfDay();       // Fin del día
                $query->whereBetween('updated_at', [$start, $end]);
            })
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);
    
        return view('livewire.entregadosadmin', [
            'admisiones' => $admisiones,
            'carteros' => $carteros,
        ]);
    }
    

    

    public function exportToPDF()
    {
        $userCity = Auth::user()->city;
        $userDepartment = Auth::user()->department;

        // Filtro por fechas y cartero seleccionado
        $admisiones = Admision::with('user')
            ->where('estado', 5)
            ->when($this->selectedCartero, function ($query) {
                $query->where('user_id', $this->selectedCartero);
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $start = Carbon::parse($this->startDate)->startOfDay(); // Inicio del día
                $end = Carbon::parse($this->endDate)->endOfDay();       // Fin del día
                $query->whereBetween('updated_at', [$start, $end]);
            })
            
            ->where(function ($query) use ($userCity, $userDepartment) {
                $query->where('reencaminamiento', $userCity)
                    ->orWhere(function ($q) use ($userCity, $userDepartment) {
                        $q->whereNull('reencaminamiento')
                            ->where(function ($q2) use ($userCity, $userDepartment) {
                                $q2->where('ciudad', $userCity)
                                    ->orWhere('destino', $userDepartment);
                            });
                    });
            })
            ->orderBy('fecha', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdfs.entregados', ['admisiones' => $admisiones]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Reporte_Entregados.pdf');
    }



    public function exportNewReportToPDF()
    {
        $query = Admision::query()
            ->when($this->startDate && $this->endDate, function ($query) {
                $start = Carbon::parse($this->startDate)->startOfDay();
                $end = Carbon::parse($this->endDate)->endOfDay();
                $query->whereBetween('updated_at', [$start, $end]);
            })
            ->when($this->selectedCartero, function ($query) {
                $query->where('user_id', $this->selectedCartero);
            });
    
        // Filtro por reencaminamiento o ciudad
        if ($this->department) {
            $query->where(function ($query) {
                $query->where('reencaminamiento', $this->department)
                      ->orWhere(function ($q) {
                          $q->whereNull('reencaminamiento')
                            ->where('ciudad', $this->department);
                      });
            });
        }
    
        // Obtener totales
        $totalEntregados = (clone $query)->where('estado', 5)->count();
        $totalFaltantes = (clone $query)->whereIn('estado', [1, 2, 3, 4, 6, 7, 8, 9, 10])->count();
        $totalVentanilla = (clone $query)->where('estado', 5)->where('direccion', 'VENTANILLA')->count();
        $totalCartero = $totalEntregados - $totalVentanilla;
    
        // Obtener admisiones entregadas
        $admisionesEntregados = (clone $query)->where('estado', 5)->orderBy('fecha', 'desc')->get();
        $admisionesVentanilla = $admisionesEntregados->filter(fn($admision) => $admision->direccion === 'VENTANILLA');
        $admisionesCartero = $admisionesEntregados->filter(fn($admision) => $admision->direccion !== 'VENTANILLA');
    
        // Obtener admisiones faltantes
        $admisionesFaltantes = (clone $query)->whereIn('estado', [1, 2, 3, 4, 6, 7, 8, 9, 10])->orderBy('fecha', 'desc')->get();
    
        // Generar el PDF
        $pdf = Pdf::loadView('pdfs.entregados_faltantes', [
            'admisionesEntregados' => $admisionesEntregados,
            'admisionesVentanilla' => $admisionesVentanilla,
            'admisionesCartero' => $admisionesCartero,
            'admisionesFaltantes' => $admisionesFaltantes,
            'totalEntregados' => $totalEntregados,
            'totalFaltantes' => $totalFaltantes,
            'totalVentanilla' => $totalVentanilla,
            'totalCartero' => $totalCartero,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'department' => $this->department,
        ]);
    
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Reporte_Entregados_Faltantes.pdf');
    }
    

    
    

}
