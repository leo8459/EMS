<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;
use App\Models\Eventos;

class Expedicion extends Component
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
    public $manifiestoEliminar;
    public $showDeleteModal;

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

        Admision::whereIn('id', $this->selectedAdmisiones)->update([
            'estado' => 12,
            'manifiesto' => null,
        ]);

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

        $this->dispatch('reloadPage');
    }
    public function eliminarManifiesto($id)
    {
        $admision = Admision::find($id);
        
        if (!$admision) {
            session()->flash('error', 'No se encontró el envío.');
            return;
        }

        $admision->update(['manifiesto' => null, 'estado' => 3]);
        
        session()->flash('message', 'El manifiesto del envío ha sido eliminado y cambiado a estado 3.');
        $this->dispatch('reloadPage');
    }
      public function eliminarPreSaca()
    {
        if (empty($this->manifiestoEliminar)) {
            session()->flash('error', 'Por favor, introduce un número de manifiesto.');
            return;
        }

        $admisiones = Admision::where('manifiesto', $this->manifiestoEliminar)->get();
        
        if ($admisiones->isEmpty()) {
            session()->flash('error', 'No se encontraron admisiones con el manifiesto ingresado.');
            return;
        }

        foreach ($admisiones as $admision) {
            $admision->update(['manifiesto' => null, 'estado' => 3]);
        }

        $this->showDeleteModal = false;
        session()->flash('message', 'El manifiesto ha sido eliminado y los envíos han cambiado a estado 3.');
        $this->dispatch('reloadPage');
    }

    public function render()
    {
        $userCity = Auth::user()->city;

        $admisiones = Admision::where('estado', 6)
            ->where('origen', $userCity)
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('manifiesto', 'like', '%' . $this->searchTerm . '%');
            })
            ->orderBy('manifiesto', 'desc')
            ->paginate($this->perPage);

        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.expedicion', [
            'admisiones' => $admisiones,
        ]);
    }
}

