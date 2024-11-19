<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;

class Recibirregional extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $perPage = 10;
    public $selectedAdmisiones = [];
    public $showModal = false;
    public $pesoEms, $pesoRegional, $observacion;
    public $selectedAdmisionesData = []; // Array para almacenar los datos seleccionados
    public $selectAll = false;



    public function render()
{
    // Obtener la ciudad del usuario autenticado
    $userCity = Auth::user()->city;

    // Filtrar las admisiones según las condiciones
    $admisiones = Admision::query()
        ->where(function ($query) use ($userCity) {
            // Condición para estado 6: Mostrar si la ciudad del usuario coincide
            $query->where('estado', 6)
                ->where('ciudad', $userCity);
        })
        ->orWhere(function ($query) use ($userCity) {
            // Condición para estado 8: Mostrar si el reencaminamiento coincide con la ciudad del usuario
            $query->where('estado', 8)
                ->where('reencaminamiento', $userCity);
        })
        ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por código
        ->orderBy('fecha', 'desc') // Ordenar por fecha descendente
        ->paginate($this->perPage);

    // Verificar si todos los elementos de la página están seleccionados
    $this->selectAll = count($admisiones) > 0 && count($this->selectedAdmisiones) === count($admisiones);

    return view('livewire.recibirregional', [
        'admisiones' => $admisiones,
    ]);
}



public function openModal()
{
    if (empty($this->selectedAdmisiones)) {
        session()->flash('error', 'Debe seleccionar al menos un envío.');
        return;
    }

    // Cargar los datos de las admisiones seleccionadas
    $this->selectedAdmisionesData = Admision::whereIn('id', $this->selectedAdmisiones)
        ->get()
        ->map(function ($admision) {
            return [
                'id' => $admision->id,
                'peso_ems' => $admision->peso_ems ?: $admision->peso,
                'peso_regional' => $admision->peso_regional,
                'observacion' => $admision->observacion,
            ];
        })->toArray();

    $this->showModal = true;
}

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['pesoEms', 'pesoRegional', 'observacion']);
    }

    public function recibirEnvios()
    {
        foreach ($this->selectedAdmisionesData as $data) {
            $admision = Admision::find($data['id']);
            if ($admision) {
                $admision->update([
                    'peso_ems' => $data['peso_ems'] ?? null,
                    'peso_regional' => $data['peso_regional'] ?? null,
                    'observacion' => $data['observacion'] ?? null,
                    'estado' => 7, // Cambiar a estado recibido
                ]);
            }
        }

        // Reiniciar datos y cerrar modal
        $this->reset(['selectedAdmisiones', 'selectedAdmisionesData']);
        $this->closeModal();

        // Mensaje de confirmación
        session()->flash('message', 'Los envíos seleccionados fueron recibidos correctamente.');
    }
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Seleccionar todas las admisiones visibles (en la página actual)
            $this->selectedAdmisiones = Admision::query()
                ->where(function ($query) {
                    // Estado 6: Mostrar si la ciudad coincide
                    $query->where('estado', 6)
                        ->where('ciudad', Auth::user()->city);
                })
                ->orWhere(function ($query) {
                    // Estado 8: Mostrar si el reencaminamiento coincide con la ciudad del usuario
                    $query->where('estado', 8)
                        ->where('reencaminamiento', Auth::user()->city);
                })
                ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Aplicar búsqueda
                ->pluck('id') // Obtener los IDs
                ->toArray();
        } else {
            // Deseleccionar todo
            $this->selectedAdmisiones = [];
        }
    }
    

}
