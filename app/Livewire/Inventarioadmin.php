<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento

class Inventarioadmin extends Component
{
    use WithPagination;
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;

    public function render()
    {
        // Filtrar y paginar los registros con estado 2
        $admisiones = Admision::where('estado', 2) // Estado específico
            ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por código
            ->orderBy('fecha', 'desc') // Ordenar por fecha
            ->paginate($this->perPage);

        // Guardar los IDs de la página actual
        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.inventarioadmin', [
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
