<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class Asignarcartero extends Component
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
        // Filtrar y paginar las admisiones en estado 3 que no están en el array de seleccionadas
        $admisiones = Admision::where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->where('estado', 3)
            ->whereNotIn('id', array_column($this->assignedAdmisiones, 'id'))
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        // Obtener todos los usuarios que serán los carteros
        $carteros = User::all();

        // Almacena los IDs de la página actual
        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.asignarcartero', [
            'admisiones' => $admisiones,
            'carteros' => $carteros,
            'assignedAdmisiones' => $this->assignedAdmisiones,
        ]);
    }

    public function selectAdmision($admisionId)
    {
        // Agregar la admisión seleccionada al array de asignadas y eliminarla de la lista de la izquierda
        $admision = Admision::find($admisionId);
        if ($admision && !in_array($admisionId, array_column($this->assignedAdmisiones, 'id'))) {
            $this->assignedAdmisiones[] = [
                'id' => $admision->id,
                'codigo' => $admision->codigo,
                'destino' => $admision->destino,
                'direccion' => $admision->direccion,
                'user_id' => $this->selectedCarteroForAll ?? null, // Asignamos el cartero seleccionado a todas las admisiones
            ];
            $this->selectedAdmisiones = array_diff($this->selectedAdmisiones, [$admisionId]);
        }
    }

    public function assignCarteroToAll()
    {
        // Asignar el cartero seleccionado a todas las admisiones en assignedAdmisiones
        foreach ($this->assignedAdmisiones as &$assignment) {
            $assignment['user_id'] = $this->selectedCarteroForAll;
        }
    }

    public function returnToLeftList($index)
    {
        // Eliminar la admisión del array de asignadas y devolverla a la lista de la izquierda
        if (isset($this->assignedAdmisiones[$index])) {
            unset($this->assignedAdmisiones[$index]);
            $this->assignedAdmisiones = array_values($this->assignedAdmisiones); // Reindexar el array
        }
    }

    public function saveAssignments()
    {
        // Guardar las asignaciones y actualizar el estado de cada admisión a 4
        foreach ($this->assignedAdmisiones as $assignment) {
            $admision = Admision::find($assignment['id']);
            if ($admision && $assignment['user_id']) {
                $admision->user_id = $assignment['user_id'];
                $admision->estado = 4;
                $admision->save();
            }
        }

        // Limpiar los arrays después de guardar
        $this->selectedAdmisiones = [];
        $this->assignedAdmisiones = [];

        session()->flash('message', 'Admisiones asignadas exitosamente.');
    }
    public function searchAdmision()
{
    $this->admisiones = Admision::where('codigo', 'like', '%' . $this->searchTerm . '%')
        ->where('estado', 3)
        ->orderBy('fecha', 'desc')
        ->paginate($this->perPage);
}
}
