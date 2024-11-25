<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use App\Models\User; // Importar el modelo User
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class Entregadosemsjota extends Component
{
    use WithPagination;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $selectedCartero = null; // Filtro por cartero
    public $startDate = null; // Fecha de inicio
    public $endDate = null;   // Fecha de fin

    public function render()
    {
        // Obtener la ciudad del usuario autenticado
        $userCity = Auth::user()->city;
    
        // Recuperar solo los carteros que pertenecen a la misma ciudad que el usuario autenticado
        $carteros = User::role('CARTERO') // Filtra usuarios con el rol "CARTERO"
            ->where('city', $userCity) // Filtra por ciudad
            ->get();
    
        $userDepartment = Auth::user()->department;
    
        // Consulta de admisiones con filtros
        $admisiones = Admision::with('user')
            ->where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->where('estado', 5)
            ->when($this->selectedCartero, function ($query) {
                $query->where('user_id', $this->selectedCartero);
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('updated_at', [$this->startDate, $this->endDate]);
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
            ->paginate($this->perPage);
    
        return view('livewire.entregadosemsjota', [
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
                $query->whereBetween('updated_at', [$this->startDate, $this->endDate]);
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
}
