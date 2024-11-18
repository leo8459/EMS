<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Livewire\WithFileUploads;

class Entregarenviosfirma extends Component
{
    use WithPagination, WithFileUploads;

    public $admision; // Almacena la admisión seleccionada
    public $photo; // Almacena la foto subida
    public $recepcionado; // Nombre de quien recibe
    public $observacion_entrega; // Observaciones
    public $id; // ID de la admisión
    public $firma; // Propiedad para capturar la firma en Base64

    /**
     * Montar el componente con los datos iniciales.
     */
    public function mount($id)
    {
        $this->admision = Admision::findOrFail($id);
        $this->recepcionado = $this->admision->recepcionado;
        $this->observacion_entrega = $this->admision->observacion_entrega;
    }

    /**
     * Guardar la admisión con los datos actualizados.
     */
    public function guardarAdmision()
    {
       
    
        // Validar datos
        $this->validate([
            'photo' => 'nullable|image|max:10240',
            'recepcionado' => 'required|string|max:255',
            'observacion_entrega' => 'nullable|string|max:1000',
            'firma' => 'required|string',
        ]);
    
        // Manejo del archivo (foto)
        $filename = null;
        if ($this->photo) {
            $filename = $this->admision->codigo . '.' . $this->photo->extension();
            $this->photo->storeAs('admisiones', $filename, 'public');
        }
    
        // Guardar en la base de datos
        $resultado = $this->admision->update([
            'estado' => 5,
            'recepcionado' => $this->recepcionado,
            'observacion_entrega' => $this->observacion_entrega,
            'firma_entrega' => $this->firma,
        ]);
    
        if ($resultado) {
            session()->flash('message', 'Admisión entregada correctamente.');
            return redirect()->route('regresar');
        } else {
            session()->flash('message', 'Error al guardar la admisión.');
        }
    }
    
    

    /**
     * Renderizar la vista Livewire.
     */
    public function render()
    {
        return view('livewire.entregarenviosfirma', [
            'admision' => $this->admision,
        ]);
    }
}
