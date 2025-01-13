<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Livewire\WithFileUploads;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento




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
            'estado' => 5, // Estado entregado
            'recepcionado' => $this->recepcionado,
            'observacion_entrega' => $this->observacion_entrega,
            'firma_entrega' => $this->firma,
            'user_id' => auth()->id(), // Guardar el ID del usuario logueado
        ]);
    
        if ($resultado) {
            // Registrar el evento
            Eventos::create([
                'accion' => 'Entregar Envío',
                'descripcion' => 'La admisión fue entregada correctamente.',
                'codigo' => $this->admision->codigo,
                'user_id' => auth()->id(),
            ]);
    
            session()->flash('message', 'Admisión entregada correctamente.');
            return redirect(request()->header('Referer'));
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

    public function noEntregado()
    {
        // Guardar sin cambiar el estado
        $this->admision->update([
            'observacion_entrega' => $this->observacion_entrega,
            'user_id' => auth()->id(), // Guardar el ID del usuario logueado
        ]);
    
        // Registrar el evento
        Eventos::create([
            'accion' => 'No Entregado',
            'descripcion' => 'La admisión permanece en el estado actual.',
            'codigo' => $this->admision->codigo,
            'user_id' => auth()->id(),
        ]);
    
        session()->flash('message', 'La admisión se mantiene sin cambios.');
        return redirect(request()->header('Referer'));
    }
        

public function return()
{
    // Cambiar estado a 10 y eliminar el user_id
    $this->admision->update([
        'estado' => 10,
        'user_id' => null, // Eliminar el user_id
    ]);

    // Registrar el evento
    Eventos::create([
        'accion' => 'Return',
        'descripcion' => 'La admisión fue marcada como Return y el usuario asignado fue eliminado.',
        'codigo' => $this->admision->codigo,
        'user_id' => auth()->id(),
    ]);

    session()->flash('message', 'La admisión fue marcada como Return y el usuario asignado fue eliminado.');
    return redirect(request()->header('Referer'));
}


}
