<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Livewire\WithFileUploads; // Importar el trait para cargas de archivos

class Entregarenviosfirma extends Component
{
    use WithPagination, WithFileUploads;

    public $admision; // Almacena la admisión seleccionada
    public $photo; // Almacena la foto subida
    public $recepcionado; // Nombre de quien recibe
    public $observacion_entrega; // Observaciones
    public $id; // ID de la admisión
    public $firma; // Nueva propiedad para capturar la firma en Base64


    public function mount($id)
    {
        // Cargar la admisión por ID
        $this->admision = Admision::findOrFail($id);
        $this->recepcionado = $this->admision->recepcionado;
        $this->observacion_entrega = $this->admision->observacion_entrega;
    }

    public function guardarAdmision()
    {
        $this->validate([
            'photo' => 'nullable|image|max:10240',
            'recepcionado' => 'required|string',
            'observacion_entrega' => 'nullable|string',
            'firma' => 'required|string', // Validar que la firma esté presente
        ]);

        if ($this->photo) {
            $filename = $this->admision->codigo . '.' . $this->photo->extension();
            $this->photo->storeAs('admisiones', $filename, 'public');
        }

        // Guardar información en la base de datos
        $this->admision->update([
            'estado' => 5,
            'recepcionado' => $this->recepcionado,
            'observacion_entrega' => $this->observacion_entrega,
            'firma_entrega' => $this->firma, // Guardar firma en Base64
        ]);

        session()->flash('message', 'Admisión entregada correctamente.');
        return redirect()->route('regresar');
    }
    
    


    public function render()
    {
        return view('livewire.entregarenviosfirma', [
            'admision' => $this->admision,
        ]);
    }
}