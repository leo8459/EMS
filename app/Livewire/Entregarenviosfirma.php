<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Admision;
use App\Models\Eventos;
use App\Models\Historico;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class Entregarenviosfirma extends Component
{
    use WithPagination, WithFileUploads;

    public $admision;
    public $photo;                   // Imagen subida
    public $recepcionado;            // Nombre de quien recibe
    public $observacion_entrega;
    public $id;
    public $firma;                   // Firma en Base64

    /**
     * Montar el componente
     */
    public function mount($id)
    {
        $this->admision = Admision::findOrFail($id);
        $this->recepcionado = $this->admision->recepcionado;
        $this->observacion_entrega = $this->admision->observacion_entrega;
    }

    /**
     * ENTREGAR ADMISION
     */
    public function guardarAdmision()
    {
        $this->validate([
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp,heic,heif|max:20480',
            'recepcionado' => 'required|string|max:255',
            'observacion_entrega' => 'nullable|string|max:1000',
            'firma' => 'nullable|string',
        ]);

        $photoBase64 = null;

        if ($this->photo) {
            try {
                $manager = new ImageManager(new Driver());

                // Leer imagen temporal (ANDROID OK)
                $jpegBinary = $manager
                    ->read($this->photo->get())
                    ->scaleDown(800)     // reduce peso
                    ->toJpeg(80)         // calidad
                    ->toString();        // binario real

                $photoBase64 = 'data:image/jpeg;base64,' . base64_encode($jpegBinary);

            } catch (\Throwable $e) {
                session()->flash('error', 'Error imagen: ' . $e->getMessage());
                return;
            }
        }

        // Dirección según rol
        $nuevaDireccion = $this->admision->direccion;
        if (auth()->user()->hasRole('VENTANILLA')) {
            $nuevaDireccion = 'VENTANILLA';
        }

        // Actualizar admisión
        $this->admision->update([
            'estado' => 5,
            'recepcionado' => $this->recepcionado,
            'observacion_entrega' => $this->observacion_entrega,
            'firma_entrega' => $this->firma,
            'user_id' => auth()->id(),
            'direccion' => $nuevaDireccion,
            'photo' => $photoBase64,
        ]);

        // Evento
        Eventos::create([
            'accion' => 'Entregar Envío',
            'descripcion' => 'La admisión fue entregada correctamente',
            'codigo' => $this->admision->codigo,
            'user_id' => auth()->id(),
        ]);

        // Histórico
        Historico::create([
            'numero_guia' => $this->admision->codigo,
            'fecha_actualizacion' => now(),
            'id_estado_actualizacion' => 6,
            'estado_actualizacion' => 'Entregado al destinatario',
        ]);

        session()->flash('message', 'Admisión entregada correctamente');

        if (auth()->user()->hasRole('VENTANILLA')) {
            return redirect()->route('entregasventanilla');
        }

        return redirect()->route('encaminocarteroentrega');
    }

    /**
     * NO ENTREGADO
     */
    public function noEntregado()
    {
        $this->validate([
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp,heic,heif|max:20480',
            'observacion_entrega' => 'nullable|string|max:1000',
        ]);

        $photoBase64 = null;

        if ($this->photo) {
            try {
                $manager = new ImageManager(new Driver());

                $jpegBinary = $manager
                    ->read($this->photo->get())
                    ->scaleDown(400)
                    ->toJpeg(80)
                    ->toString();

                $photoBase64 = 'data:image/jpeg;base64,' . base64_encode($jpegBinary);

            } catch (\Throwable $e) {
                session()->flash('error', 'Error imagen: ' . $e->getMessage());
                return;
            }
        }

        $this->admision->update([
            'observacion_entrega' => $this->observacion_entrega,
            'user_id' => auth()->id(),
            'photo' => $photoBase64,
        ]);

        Eventos::create([
            'accion' => 'No Entregado',
            'descripcion' => 'La admisión no fue entregada',
            'codigo' => $this->admision->codigo,
            'user_id' => auth()->id(),
        ]);

        session()->flash('message', 'La admisión quedó como NO ENTREGADA');
        return redirect()->back();
    }

    /**
     * RETURN
     */
    public function return()
    {
        $this->admision->update([
            'estado' => 10,
            'user_id' => null,
        ]);

        Eventos::create([
            'accion' => 'Return',
            'descripcion' => 'La admisión fue marcada como Return',
            'codigo' => $this->admision->codigo,
            'user_id' => auth()->id(),
        ]);

        Historico::create([
            'numero_guia' => $this->admision->codigo,
            'fecha_actualizacion' => now(),
            'id_estado_actualizacion' => 7,
            'estado_actualizacion' => 'Sin acceso al lugar de entrega',
        ]);

        session()->flash('message', 'La admisión fue marcada como RETURN');
        return redirect()->back();
    }

    /**
     * Render
     */
    public function render()
    {
        return view('livewire.entregarenviosfirma', [
            'admision' => $this->admision,
        ]);
    }
}
