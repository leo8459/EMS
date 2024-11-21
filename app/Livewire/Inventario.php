<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento

class Inventario extends Component
{
    use WithPagination;
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;

    public function render()
    {
        // Obtener la ciudad del usuario autenticado
        $userCity = Auth::user()->city;
    
        // Filtrar y paginar los registros
        $admisiones = Admision::where('origen', $userCity) // Filtrar por la ciudad del usuario
            ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por código
            ->where('estado', 2) // Estado específico
            ->orderBy('fecha', 'desc') // Ordenar por fecha
            ->paginate($this->perPage);
    
        // Guardar los IDs de la página actual
        $this->currentPageIds = $admisiones->pluck('id')->toArray();
    
        return view('livewire.inventario', [
            'admisiones' => $admisiones,
        ]);
    }
    
    public function devolverAdmision($id)
    {
        $admision = Admision::find($id);
    
        if ($admision) {
            // Cambiar el estado de la admisión
            $admision->estado = 1;
            $admision->save();
    
            // Registrar el evento
            Eventos::create([
                'accion' => 'Devolver',
                'descripcion' => 'La admisión fue devuelta a Ventanilla.',
                'codigo' => $admision->codigo,
                'user_id' => Auth::id(),
            ]);
    
            session()->flash('message', 'La admisión ha sido devuelta exitosamente.');
        } else {
            session()->flash('error', 'La admisión no pudo ser encontrada.');
        }
    }
    
}
