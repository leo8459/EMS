<?php

namespace App\Livewire;


use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\WithFileUploads; // Añade esto
use Livewire\Component;

class Encaminocarteroentrega extends Component
{
    use WithPagination, WithFileUploads; // Añade WithFileUploads
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $showModal = false; // Añade esta propiedad
    public $selectedAdmision; // Para almacenar la admisión seleccionada
    public $photo; // Propiedad para la foto
    public $recepcionado;
    public $observacion_entrega;
    


    public function render()
{
    // Filtrar y paginar las admisiones en estado 4 y relacionadas con el usuario autenticado
    $admisiones = Admision::with('user') // Aseguramos que la relación user esté cargada
        ->where('codigo', 'like', '%' . $this->searchTerm . '%')
        ->where('estado', 4)
        ->where('user_id', Auth::id()) // Filtra por el usuario logueado
        ->orderBy('fecha', 'desc')
        ->paginate($this->perPage);

    return view('livewire.encaminocarteroentrega', [
        'admisiones' => $admisiones,
    ]);
}

    public function openModal($id)
    {
        $this->admisionId = $id;
        $this->selectedAdmision = Admision::findOrFail($id);
        $this->showModal = true; // Establece el modal para que se muestre
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

