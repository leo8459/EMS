<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class Recibir extends Component
{
    use WithPagination;
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $selectedAdmisiones = []; // Para almacenar los IDs seleccionados
    public $selectAll = false; // Añadido para controlar el seleccionar todo


    public function render()
    {
        // Filtrar y paginar los registros
        $admisiones = Admision::where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->where('estado', 2)
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        // Almacena los IDs de la página actual
        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.recibir', [
            'admisiones' => $admisiones,
        ]);
    }
    public function recibirAdmision()
    {
        if (!empty($this->selectedAdmisiones)) {
            Admision::whereIn('id', $this->selectedAdmisiones)
                ->update(['estado' => 3]);

            $this->selectedAdmisiones = [];
            $this->selectAll = false; // Desmarcar el seleccionar todo después de actualizar
            session()->flash('message', 'Las admisiones seleccionadas han sido recibidas.');
            $this->render(); // Refrescar la vista
        } else {
            session()->flash('error', 'Seleccione al menos una admisión.');
        }
    }
    public function updatedSelectAll($value)
    {
        $this->selectAllItems($value);
    }
    public function selectAllItems($value)
{
    if ($value) {
        // Selecciona todos los IDs visibles
        $this->selectedAdmisiones = $this->currentPageIds;
    } else {
        // Deselecciona todos los IDs
        $this->selectedAdmisiones = [];
    }
}
}
