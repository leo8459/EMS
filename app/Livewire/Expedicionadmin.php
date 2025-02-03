<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;
use App\Models\Eventos; // Asegúrate de importar el modelo Eventos

class Expedicionadmin extends Component
{
    use WithPagination;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10000000;
    public $admisionId;
    public $selectedAdmisiones = [];
    public $selectAll = false;
    public $showModal = false;
    public $codigoRetenido;
    public $observacionRetencion;

    public function openRetenerModal()
    {
        if (empty($this->selectedAdmisiones)) {
            session()->flash('error', 'Por favor, selecciona al menos un envío.');
            return;
        }

        $this->codigoRetenido = Admision::whereIn('id', $this->selectedAdmisiones)->pluck('codigo')->implode(', ');
        $this->showModal = true;
    }

    public function retenerEnvios()
    {
        if (empty($this->selectedAdmisiones)) {
            session()->flash('error', 'Por favor, selecciona al menos un envío.');
            return;
        }

        // Actualizar estado de las admisiones seleccionadas
        Admision::whereIn('id', $this->selectedAdmisiones)->update([
            'estado' => 12,
            'manifiesto' => null,
        ]);

        // Registrar eventos y actualizar observación por cada admisión
        foreach ($this->selectedAdmisiones as $id) {
            $admision = Admision::find($id);
            if ($admision) {
                $admision->observacion = $this->observacionRetencion;
                $admision->save();

                Eventos::create([
                    'accion' => 'Retenido',
                    'descripcion' => 'Envío retenido con código: ' . $admision->codigo,
                    'codigo' => $admision->codigo,
                    'user_id' => Auth::id(),
                ]);
            }
        }

        $this->reset(['selectedAdmisiones', 'showModal', 'observacionRetencion', 'codigoRetenido']);
        session()->flash('message', 'Los envíos seleccionados han sido retenidos.');

        // Forzar recarga del navegador
        $this->dispatch('reloadPage');
    }

    public function render()
    {
        $admisiones = Admision::where('estado', 6) // Filtrar solo estado 6
            ->when($this->searchTerm, function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('manifiesto', 'like', '%' . $this->searchTerm . '%');
            })
            ->orderBy('manifiesto', 'desc')
            ->paginate($this->perPage);
    
        $this->currentPageIds = $admisiones->pluck('id')->toArray();
    
        return view('livewire.expedicionadmin', [
            'admisiones' => $admisiones,
        ]);
    }
    
}
