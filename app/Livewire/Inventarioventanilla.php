<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;

class Inventarioventanilla extends Component
{
    use WithPagination;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
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
        $userCity = Auth::user()->city;

        $admisiones = Admision::where(function ($query) use ($userCity) {
                $query->where('reencaminamiento', $userCity)
                    ->orWhere(function ($subQuery) use ($userCity) {
                        $subQuery->whereNull('reencaminamiento')
                                 ->where('ciudad', $userCity);
                    });
            })
            ->where('estado', 11)
            ->where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.inventarioventanilla', [
            'admisiones' => $admisiones,
        ]);
    }

    public function acceptSelected()
    {
        if (count($this->selectedAdmisiones) > 0) {
            Admision::whereIn('id', $this->selectedAdmisiones)
                ->update(['estado' => 9]);
            session()->flash('message', 'Los envíos seleccionados fueron aceptados correctamente.');
            $this->selectedAdmisiones = []; // Limpiar selección tras aceptar
    
            // Recargar la página
            return redirect(request()->header('Referer'));
        } else {
            session()->flash('error', 'No se seleccionó ningún envío.');
        }
    }
    
}
