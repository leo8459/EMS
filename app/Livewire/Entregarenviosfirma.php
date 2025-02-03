<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Livewire\WithFileUploads;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento
use App\Models\Historico; // Asegúrate de importar el modelo Evento




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
            'firma' => 'nullable|string',
        ]);

        // Manejo del archivo (foto)
        $filename = null;
        if ($this->photo) {
            $filename = $this->admision->codigo . '.' . $this->photo->extension();
            // 'fotos' es la subcarpeta dentro de storage/app/public
            $this->photo->storeAs('fotos', $filename, 'public');
            
        }

        // Determinar la nueva dirección según el rol
        $nuevaDireccion = $this->admision->direccion; // Por defecto, mantiene la misma
        if (auth()->user()->hasRole('VENTANILLA')) {
            $nuevaDireccion = 'VENTANILLA';
        }

        // Guardar en la base de datos
        $resultado = $this->admision->update([
            'estado' => 5, // Estado entregado
            'recepcionado' => $this->recepcionado,
            'observacion_entrega' => $this->observacion_entrega,
            'firma_entrega' => $this->firma,
            'user_id' => auth()->id(), // Guardar el ID del usuario logueado
            'direccion' => $nuevaDireccion, // Actualizar dirección según el rol
        ]);

        if ($resultado) {
            // Registrar el evento
            Eventos::create([
                'accion' => 'Entregar Envío',
                'descripcion' => 'La admisión fue entregada correctamente.',
                'codigo' => $this->admision->codigo,
                'user_id' => auth()->id(),
            ]);
            Historico::create([
                'numero_guia' => $this->admision->codigo, // Usar $this-> para acceder a la propiedad
                'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
                'id_estado_actualizacion' => 6, // Estado inicial: 7 (Sin acceso al lugar de entrega)
                'estado_actualizacion' => 'Entregado al destinatario', // Descripción del estado
            ]);

            session()->flash('message', 'Admisión entregada correctamente.');

            // Redirigir según el rol del usuario
            if (auth()->user()->hasRole('VENTANILLA')) {
                return redirect()->route('entregasventanilla');
            } else {
                return redirect()->route('encaminocarteroentrega');
            }
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
        // Validar los datos, incluyendo la imagen (photo)
        $this->validate([
            'photo' => 'nullable|image|max:10240',
            'observacion_entrega' => 'nullable|string|max:1000',
        ]);
    
        // Manejo del archivo (foto) de la misma manera que en guardarAdmision()
        $filename = null;
        if ($this->photo) {
            // Se utiliza el código del envío para nombrar el archivo
            $filename = $this->admision->codigo . '.' . $this->photo->extension();
            // Se almacena la imagen en la subcarpeta 'fotos' dentro del disco 'public'
            $this->photo->storeAs('fotos', $filename, 'public');
        }
    
        // Actualizar la admisión, se mantiene el estado actual, pero se actualizan observaciones y se asigna el usuario
        // Si deseas guardar el nombre del archivo en un campo específico de la base de datos, agrega el campo correspondiente
        $this->admision->update([
            'observacion_entrega' => $this->observacion_entrega,
            'user_id' => auth()->id(),
            // Por ejemplo, si tienes un campo 'foto', podrías agregar:
            // 'foto' => $filename,
        ]);
    
        // Registrar el evento correspondiente
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
        Historico::create([
            'numero_guia' => $admision->codigo, // Asignar el código único de admisión al número de guía
            'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
            'id_estado_actualizacion' => 7, // Estado inicial: 1
            'estado_actualizacion' => ' Sin acceso al lugar de entrega', // Descripción del estado
        ]);

        session()->flash('message', 'La admisión fue marcada como Return y el usuario asignado fue eliminado.');
        return redirect(request()->header('Referer'));
    }
}
