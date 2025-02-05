<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Eventos;
use App\Models\User;

class Eventosregistro extends Component
{
    use WithPagination;
    
    public $currentPageIds = [];
    public $searchTerm = '';
    public $searchUserId = ''; // Nuevo campo para búsqueda por usuario
    public $perPage = 10;

    public function render()
    {
        $query = Eventos::query();

        // Filtro por código
        if (!empty($this->searchTerm)) {
            $query->where('codigo', 'like', '%' . $this->searchTerm . '%');
        }

        // Filtro por usuario
        if (!empty($this->searchUserId)) {
            $query->where('user_id', $this->searchUserId);
        }

        // Ordenar y paginar los resultados
        $admisiones = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        // Obtener los usuarios para el select
        $usuarios = User::all();

        return view('livewire.eventosregistro', [
            'admisiones' => $admisiones,
            'usuarios' => $usuarios, // Pasar usuarios a la vista
        ]);
    }
}
