<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;

class Entregasventanillaadmin extends Component
{
    use WithPagination;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $selectedAdmisiones = [];
    public $showModal = false;
    public $destinoModal;
    public $ciudadModal;
    public $selectedAdmisionesCodes = [];
    public $selectAll = false;
    public $showReencaminamientoModal = false;
    public $selectedDepartment = null;
    public $lastSearchTerm = '';
    public $selectedCity = null;
    public $cityJustUpdated = false;

    public function render()
    {
        // Filtrar y paginar los registros con estado 9
        $admisiones = Admision::where('estado', 9) // Solo registros con estado 9
            ->when($this->searchTerm, function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%'); // Filtro por término de búsqueda
            })
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        // Almacena los IDs de la página actual
        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.entregasventanillaadmin', [
            'admisiones' => $admisiones,
        ]);
    }
}
