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
use App\Models\Historico; // Asegúrate de importar el modelo Evento




class Asignarsecartero extends Component
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
    $userCity = Auth::user()->city; // Ciudad del usuario autenticado
    $userId = Auth::id(); // ID del usuario autenticado
    
    // Obtener los IDs de las admisiones ya asignadas
    $assignedIds = array_column($this->assignedAdmisiones, 'id');
    
    // Filtrar y paginar las admisiones según las condiciones
    $admisiones = Admision::query()
        ->where(function ($query) use ($userCity) {
            // Condición para estado 7
            $query->where(function ($subQuery) use ($userCity) {
                $subQuery->where('estado', 7)
                         ->where(function ($innerQuery) use ($userCity) {
                             $innerQuery->where('reencaminamiento', $userCity) // Si hay reencaminamiento, usarlo
                                        ->orWhere(function ($orQuery) use ($userCity) {
                                            $orQuery->whereNull('reencaminamiento') // Si no hay reencaminamiento
                                                   ->where('ciudad', $userCity);    // Usar ciudad
                                        });
                         });
            })
            ->orWhere(function ($subQuery) use ($userCity) {
                // Condición para estado 3
                $subQuery->where('estado', 3)
                         ->where('origen', $userCity); // Usar origen
            });
        })
        ->whereNotIn('id', $assignedIds) // Excluir las admisiones ya asignadas
        ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por código
        ->orderBy('fecha', 'desc') // Ordenar por fecha descendente
        ->paginate($this->perPage);
    
    // Obtener solo el usuario autenticado como cartero
    $carteros = User::where('id', $userId)->get();
    
    return view('livewire.asignarsecartero', [
        'admisiones' => $admisiones,
        'carteros' => $carteros, // Solo el usuario autenticado
        'assignedAdmisiones' => $this->assignedAdmisiones,
    ]);
}

    

    

    

   
    public function selectAdmision($admisionId)
{
    // Buscar la admisión seleccionada
    $admision = Admision::find($admisionId);

    if ($admision) {
        // Agregar la admisión al array de asignadas si no está ya agregada
        if (!in_array($admisionId, array_column($this->assignedAdmisiones, 'id'))) {
            $this->assignedAdmisiones[] = [
                'id' => $admision->id,
                'codigo' => $admision->codigo,
                'destino' => $admision->destino,
                'direccion' => $admision->direccion,
                'user_id' => $this->selectedCarteroForAll ?? null, // Asignar el cartero seleccionado a todas las admisiones
            ];
        }

        // Actualizar la lista principal eliminando la admisión seleccionada
        $this->currentPageIds = array_diff($this->currentPageIds, [$admisionId]);
    }
}

    

public function assignCarteroToAll()
{
    // Verificar si se seleccionó un cartero para asignar a todos
    if ($this->selectedCarteroForAll) {
        foreach ($this->assignedAdmisiones as &$assignment) {
            $assignment['user_id'] = $this->selectedCarteroForAll;
        }
    } else {
        session()->flash('error', 'Debe seleccionar un cartero para asignar a todos.');
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
        if ($admision && isset($assignment['user_id'])) {
            $admision->user_id = $assignment['user_id'];
            $admision->estado = 4;
            $admision->save();

            // Obtener el nombre del cartero asignado
            $cartero = User::find($assignment['user_id']);

            // Registrar el evento
            Eventos::create([
                'accion' => 'Asignar Cartero',
                'descripcion' => "Envio asignado a " . ($cartero ? $cartero->name : 'Desconocido'),
                'codigo' => $admision->codigo,
                'user_id' => Auth::id(),
            ]);
            Historico::create([
                'numero_guia' => $admision->codigo, // Asignar el código único de admisión al número de guía
                'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
                'id_estado_actualizacion' => 5, // Estado inicial: 1
                'estado_actualizacion' => 'Fuera para entrega', // Descripción del estado
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
