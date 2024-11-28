<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;

class Entregasventanilla extends Component
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
        // Obtener la ciudad del usuario logueado
        $userCity = Auth::user()->city; // Asume que el modelo User tiene una columna 'city'

        // Filtrar y paginar los registros
        $admisiones = Admision::where(function ($query) use ($userCity) {
                // Verificar si reencaminamiento coincide con la ciudad del usuario
                $query->where('reencaminamiento', $userCity)
                    // Si reencaminamiento está vacío, verificar si ciudad coincide con la del usuario
                    ->orWhere(function ($subQuery) use ($userCity) {
                        $subQuery->whereNull('reencaminamiento')
                                 ->where('ciudad', $userCity);
                    });
            })
            ->where('estado', 9) // Solo registros con estado 9
            ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por término de búsqueda
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        // Almacena los IDs de la página actual
        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.entregasventanilla', [
            'admisiones' => $admisiones,
        ]);
    }
}
