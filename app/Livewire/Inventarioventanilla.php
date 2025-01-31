<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;
use App\Models\Eventos;
use App\Models\Historico; // Asegúrate de importar el modelo Evento

class Inventarioventanilla extends Component
{
    use WithPagination;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $selectedAdmisiones = []; // Solo mantiene los IDs que se seleccionen manualmente
    public $showModal = false;
    public $destinoModal;
    public $ciudadModal;
    public $selectedAdmisionesCodes = [];
    public $selectAll = false;
    public $showReencaminamientoModal = false;
    public $selectedDepartment = null;
    public $lastSearchTerm = '';
    public $selectedCity = null;
    public $cityJustUpdated = false;

    public function render()
    {
        $userCity = Auth::user()->city;

        $admisiones = Admision::where(function ($query) use ($userCity) {
                $query->where('reencaminamiento', $userCity)
                    ->orWhere(function ($subQuery) use ($userCity) {
                        $subQuery->whereNull('reencaminamiento')
                                 ->where('ciudad', $userCity);
                    });
            })
            ->where('estado', 11)
            ->where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.inventarioventanilla', [
            'admisiones' => $admisiones,
        ]);
    }

    public function acceptSelected()
    {
        if (count($this->selectedAdmisiones) > 0) {
            // Actualizar estado de las admisiones seleccionadas
            Admision::whereIn('id', $this->selectedAdmisiones)
                ->update(['estado' => 9]);
    
            // Registrar el evento para cada admisión seleccionada
            foreach ($this->selectedAdmisiones as $admisionId) {
                $admision = Admision::find($admisionId); // Buscar la admisión por ID
                if ($admision) {
                    Eventos::create([
                        'accion' => 'Aceptar Envios',
                        'descripcion' => 'El envio fue recibido por ventanilla.',
                        'codigo' => $admision->codigo,
                        'user_id' => Auth::id(),
                    ]);
                    Historico::create([
                        'numero_guia' => $admision->codigo, // Asignar el código único de admisión al número de guía
                        'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
                        'id_estado_actualizacion' => 10, // Estado inicial: 1
                        'estado_actualizacion' => 'Disponible para recogida', // Descripción del estado
                    ]);
                }
            }
    
            session()->flash('message', 'Los envíos seleccionados fueron aceptados correctamente.');
            $this->selectedAdmisiones = []; // Limpiar selección tras aceptar
    
            // Recargar la página
            $this->dispatch('reload-page');
        } else {
            session()->flash('error', 'No se seleccionó ningún envío.');
        }
    }
    
    
}
