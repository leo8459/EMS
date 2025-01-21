<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;

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
        $admision->observacion = $this->observacionRetencion;
        $admision->save();
    }

    $this->reset(['selectedAdmisiones', 'showModal', 'observacionRetencion', 'codigoRetenido']);
    session()->flash('message', 'Los envíos seleccionados han sido retenidos.');

    // Forzar recarga del navegador
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
