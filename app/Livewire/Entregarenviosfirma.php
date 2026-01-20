<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Livewire\WithFileUploads;
use App\Models\Eventos;
use App\Models\Historico;
use Intervention\Image\ImageManagerStatic as Image;

class Entregarenviosfirma extends Component
{
    use WithPagination, WithFileUploads;

    public $admision;
    public $photo;             // Almacena la foto subida (archivo)
    public $recepcionado;      // Nombre de quien recibe
    public $observacion_entrega; 
    public $id;
    public $firma;             // Firma en Base64

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
            'photo' => 'nullable|image|max:20480',
            'recepcionado' => 'required|string|max:255',
            'observacion_entrega' => 'nullable|string|max:1000',
            'firma' => 'nullable|string',
        ]);

        // Convirtiendo la imagen a Base64 si existe
        $photoBase64 = null;
        if ($this->photo) {
            try {
                $image = Image::make($this->photo->getRealPath())->fit(400, 400, function ($constraint) {
                    $constraint->upsize();
                });
                $photoBase64 = (string) $image->encode('data-url');
            } catch (\Exception $e) {
                session()->flash('error', 'Error al procesar la imagen: ' . $e->getMessage());
                return;
            }
        }

        // Determinar la nueva dirección según el rol
        $nuevaDireccion = $this->admision->direccion;
        if (auth()->user()->hasRole('VENTANILLA')) {
            $nuevaDireccion = 'VENTANILLA';
        }

        // Guardar en la base de datos
        $resultado = $this->admision->update([
            'estado' => 5, // Estado entregado
            'recepcionado' => $this->recepcionado,
            'observacion_entrega' => $this->observacion_entrega,
            'firma_entrega' => $this->firma,
            'user_id' => auth()->id(),       // Guardar el ID del usuario logueado
            'direccion' => $nuevaDireccion,  // Actualizar dirección según el rol
            // Aquí guardas el Base64 en la columna "photo" de tu tabla (si ese es el nombre)
            'photo' => $photoBase64,
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
                'numero_guia' => $this->admision->codigo,
                'fecha_actualizacion' => now(),
                'id_estado_actualizacion' => 6, 
                'estado_actualizacion' => 'Entregado al destinatario',
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

    public function noEntregado()
    {
        // Validar datos, incluyendo la imagen (photo)
        $this->validate([
            'photo' => 'nullable|image|max:20480',
            'observacion_entrega' => 'nullable|string|max:1000',
        ]);

        // Convertir la imagen a Base64 (si existe)
        $photoBase64 = null;
        if ($this->photo) {
            try {
                $image = Image::make($this->photo->getRealPath())->fit(400, 400, function ($constraint) {
                    $constraint->upsize();
                });
                $photoBase64 = (string) $image->encode('data-url');
            } catch (\Exception $e) {
                session()->flash('error', 'Error al procesar la imagen: ' . $e->getMessage());
                return;
            }
        }

        // Actualizar la admisión con la observación y la posible foto en Base64
        $this->admision->update([
            'observacion_entrega' => $this->observacion_entrega,
            'user_id' => auth()->id(),
            'photo' => $photoBase64,
        ]);

        // Registrar evento
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
            'user_id' => null,
        ]);

        // Registrar evento
        Eventos::create([
            'accion' => 'Return',
            'descripcion' => 'La admisión fue marcada como Return y el usuario asignado fue eliminado.',
            'codigo' => $this->admision->codigo,
            'user_id' => auth()->id(),
        ]);

        Historico::create([
            'numero_guia' => $this->admision->codigo,
            'fecha_actualizacion' => now(),
            'id_estado_actualizacion' => 7,
            'estado_actualizacion' => 'Sin acceso al lugar de entrega',
        ]);

        session()->flash('message', 'La admisión fue marcada como Return y el usuario asignado fue eliminado.');
        return redirect(request()->header('Referer'));
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
