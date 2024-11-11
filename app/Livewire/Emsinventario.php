<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
class Emsinventario extends Component
{
    use WithPagination;
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;

    public function render()
    {
        // Filtrar y paginar las admisiones en estado 3
        $admisiones = Admision::with('user') // Aseguramos que la relación user esté cargada
            ->where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->where('estado', 3)
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);
    
        return view('livewire.emsinventario', [
            'admisiones' => $admisiones,
        ]);
    }
}
