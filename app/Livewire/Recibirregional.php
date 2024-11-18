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

    public function render()
    {
        // Obtener la ciudad del usuario logueado
        $userCity = Auth::user()->city;

        // Filtrar las admisiones por la ciudad del usuario y por el estado 6
        $admisiones = Admision::where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->where('estado', 6)
            ->where('ciudad', $userCity) // Filtro por la ciudad del usuario
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

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

        // Cargar los datos de todas las admisiones seleccionadas
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
}
