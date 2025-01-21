<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;
use App\Models\Eventos;

class Expedicion extends Component
{
    use WithPagination;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10000000;
    public $admisionId;
    public $selectedAdmisiones = []; // Solo mantiene los IDs que se seleccionen manualmente
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
        // Recupera la ciudad del usuario logueado
        $userCity = Auth::user()->city;
    
        // Recupera las admisiones con estado 6 y origen igual a la ciudad del usuario
        $admisiones = Admision::where('estado', 6)
            ->where('origen', $userCity)
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('manifiesto', 'like', '%' . $this->searchTerm . '%');
            })
            ->orderBy('manifiesto', 'desc') // Ordenar por manifiesto de mayor a menor
            ->paginate($this->perPage); // Paginación
    
        $this->currentPageIds = $admisiones->pluck('id')->toArray();
    
        return view('livewire.expedicion', [
            'admisiones' => $admisiones,
        ]);
    }
    
    

    
}
