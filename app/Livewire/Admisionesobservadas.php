<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use App\Models\Eventos;
use Illuminate\Support\Facades\Auth;

class Admisionesobservadas extends Component
{
    use WithPagination;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10000000;
    public $selectedAdmisiones = [];
    public $selectAll = false;
    public $showModal = false;
    public $codigoRetenido;
    public $observacionRetencion;
    public $admisionId;

    public function openRetenerModal($admisionId)
    {
        $this->admisionId = $admisionId;
        $admision = Admision::find($admisionId);

        if ($admision) {
            $this->codigoRetenido = $admision->codigo;
            $this->observacionRetencion = $admision->observacion_entrega;
        }

        $this->showModal = true;
    }

    public function guardarRetencion()
    {
        $admision = Admision::find($this->admisionId);

        if ($admision) {
            $admision->update([
                'observacion_entrega' => $this->observacionRetencion,
                'estado' => 3,
            ]);

            session()->flash('message', 'El envío ha sido actualizado correctamente.');

            // Registrar el evento
            Eventos::create([
                'accion' => 'Devolver Envio Retenido',
                'descripcion' => 'El Envio fue Devuelto a Inventario',
                'codigo' => $admision->codigo,
                'user_id' => Auth::id(),
            ]);
        } else {
            session()->flash('error', 'El envío no existe.');
        }

        $this->resetModal();
    }

    private function resetModal()
    {
        $this->showModal = false;
        $this->admisionId = null;
        $this->codigoRetenido = '';
        $this->observacionRetencion = '';
    }

    public function render()
    {
        $user = Auth::user(); // Obtener usuario autenticado
    
        $admisiones = Admision::where('origen', $user->city) // Coincidir 'origen' de Admision con 'city' del usuario
            ->whereNotNull('notificacion') // Solo registros con notificación escrita
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('manifiesto', 'like', '%' . $this->searchTerm . '%');
            })
            ->orderBy('manifiesto', 'desc')
            ->paginate($this->perPage);
    
        $this->currentPageIds = $admisiones->pluck('id')->toArray();
    
        return view('livewire.admisionesobservadas', [
            'admisiones' => $admisiones,
        ]);
    }
    
}
