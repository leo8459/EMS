<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Livewire\WithFileUploads;
use App\Models\Eventos;

class Eventosregistro extends Component
{
    use WithPagination;
    
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $selectedAdmisiones = []; // Array para almacenar las admisiones seleccionadas
    public $assignedAdmisiones = []; // Array para almacenar las admisiones asignadas temporalmente
    public $selectedCarteroForAll; // Nuevo campo para seleccionar cartero para todas las admisiones



    public function render()
    {
        // Obtener todos los registros de la tabla 'eventos', ordenados por 'created_at' de forma descendente
        $admisiones = Eventos::where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtrar por código si hay búsqueda
            ->orderBy('created_at', 'desc') // Ordenar por 'created_at' de forma descendente
            ->paginate($this->perPage); // Paginación
    
        // Almacena los IDs de la página actual
        $this->currentPageIds = $admisiones->pluck('id')->toArray();
    
        return view('livewire.eventosregistro', [
            'admisiones' => $admisiones,
        ]);
    }
    
}
