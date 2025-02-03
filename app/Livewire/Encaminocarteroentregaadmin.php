<?php

namespace App\Livewire;

use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\WithFileUploads;
use Livewire\Component;

class Encaminocarteroentregaadmin extends Component
{
    use WithPagination, WithFileUploads;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $showModal = false;
    public $selectedAdmision;
    public $photo;
    public $recepcionado;
    public $observacion_entrega;

    public function render()
    {
        // Filtrar y paginar todas las admisiones con estados 4 y 10
        $admisiones = Admision::with('user') // Aseguramos que la relación user esté cargada
            ->whereIn('estado', [4, 10]) // Filtra por estados 4 y 10
            ->when($this->searchTerm, function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%');
            })
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        return view('livewire.encaminocarteroentregaadmin', [
            'admisiones' => $admisiones,
        ]);
    }

    public function openModal($id)
    {
        $this->admisionId = $id;
        $this->selectedAdmision = Admision::findOrFail($id);
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'photo' => 'image|max:10240',
            'recepcionado' => 'required|string',
            'observacion_entrega' => 'nullable|string',
        ]);

        if ($this->photo) {
            $filename = $this->selectedAdmision->codigo . '.' . $this->photo->extension();
            $this->photo->storeAs('', $filename, 'public_fotos');
        }

        // Intentar actualizar el estado y otros campos
        $this->selectedAdmision->update([
            'estado' => 5,
            'recepcionado' => $this->recepcionado,
            'observacion_entrega' => $this->observacion_entrega,
        ]);

        // Debug: Verificar si el estado fue actualizado
        if ($this->selectedAdmision->fresh()->estado == 5) {
            session()->flash('message', 'Foto subida y admisión actualizada correctamente.');
        } else {
            session()->flash('error', 'No se pudo actualizar el estado de la admisión.');
        }

        $this->reset(['photo', 'selectedAdmision', 'recepcionado', 'observacion_entrega']);
        $this->showModal = false;
    }
}
