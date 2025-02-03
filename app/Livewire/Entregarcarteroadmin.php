<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;

class Entregarcarteroadmin extends Component
{
    use WithPagination;
    
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;

    public function render()
    {
        // Obtener todas las admisiones en estado 5
        $admisiones = Admision::with('user') // Aseguramos que la relación user esté cargada
            ->where('estado', 5) // Filtro por estado 5
            ->when($this->searchTerm, function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%'); // Filtro opcional por código
            })
            ->orderBy('fecha', 'desc') // Ordenar por fecha descendente
            ->paginate($this->perPage);

        return view('livewire.entregarcarteroadmin', [
            'admisiones' => $admisiones,
        ]);
    }
}
