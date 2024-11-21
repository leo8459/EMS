<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento




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
        // Obtener la ciudad del usuario autenticado
        $userCity = Auth::user()->city;
    
        // Filtrar y paginar las admisiones
        $admisiones = Admision::where(function ($query) use ($userCity) {
                $query->where(function ($subQuery) use ($userCity) {
                    // Condición para estado 3: Mostrar si el origen coincide y excluir si la ciudad coincide
                    $subQuery->where('estado', 3)
                             ->where('origen', $userCity) // Mostrar si el origen coincide
                             ->where('ciudad', '!=', $userCity); // Excluir si la ciudad coincide
                })
                ->orWhere(function ($subQuery) use ($userCity) {
                    // Condición para estado 7: Mostrar si la ciudad coincide y excluir si el origen coincide
                    $subQuery->where('estado', 7)
                             ->where('ciudad', $userCity) // Mostrar si la ciudad coincide
                             ->where('origen', '!=', $userCity); // Excluir si el origen coincide
                });
            })
            ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por código
            ->whereNotIn('id', array_column($this->assignedAdmisiones, 'id')) // Excluir los ya asignados
            ->orderBy('fecha', 'desc') // Ordenar por fecha
            ->paginate($this->perPage);
    
        // Filtrar los carteros para que solo muestren los que están en la misma ciudad del usuario logueado
        $carteros = User::where('city', $userCity)->get();
    
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

            // Registrar el evento
            Eventos::create([
                'accion' => 'Asignar Cartero',
                'descripcion' => "La admisión fue asignada al cartero con ID: {$assignment['user_id']}.",
                'codigo' => $admision->codigo,
                'user_id' => Auth::id(),
            ]);
        }
    }

    // Limpiar los arrays después de guardar
    $this->selectedAdmisiones = [];
    $this->assignedAdmisiones = [];

    session()->flash('message', 'Admisiones asignadas exitosamente.');
}


public function searchAdmision()
{
    // Implementar la misma lógica de filtros en la búsqueda
    $userCity = Auth::user()->city;

    $this->admisiones = Admision::where(function ($query) use ($userCity) {
            $query->where(function ($subQuery) use ($userCity) {
                $subQuery->where('estado', 3)
                         ->where('origen', $userCity)
                         ->where('ciudad', '!=', $userCity);
            })
            ->orWhere(function ($subQuery) use ($userCity) {
                $subQuery->where('estado', 7)
                         ->where('ciudad', $userCity)
                         ->where('origen', '!=', $userCity);
            });
        })
        ->where('codigo', 'like', '%' . $this->searchTerm . '%')
        ->orderBy('fecha', 'desc')
        ->paginate($this->perPage);
}
}
