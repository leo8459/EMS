<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class Entregarcartero extends Component
{
    use WithPagination;
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;

    public function render()
    {
        // Obtener las admisiones del usuario autenticado en estado 5
        $admisiones = Admision::with('user') // Aseguramos que la relación user esté cargada
            ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por código
            ->where('estado', 5) // Filtro por estado 5
            ->where('user_id', Auth::id()) // Filtro por usuario autenticado
            ->orderBy('fecha', 'desc') // Ordenar por fecha descendente
            ->paginate($this->perPage);
    
        return view('livewire.entregarcartero', [
            'admisiones' => $admisiones,
        ]);
    }
    
}
