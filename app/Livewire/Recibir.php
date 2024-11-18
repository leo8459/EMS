<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class Recibir extends Component
{
    use WithPagination;
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $selectedAdmisiones = []; // Para almacenar los IDs seleccionados
    public $selectAll = false; // Añadido para controlar el seleccionar todo
    public $showModal = false;
    public $admissionData = [];
    

    public function render()
    {
        // Obtener la ciudad del usuario autenticado
        $userCity = Auth::user()->city;
    
        // Filtrar y paginar los registros
        $admisiones = Admision::where('origen', $userCity) // Filtrar por ciudad del usuario
            ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por término de búsqueda
            ->where('estado', 2) // Estado específico
            ->orderBy('fecha', 'desc') // Ordenar por fecha
            ->paginate($this->perPage);
    
        // Guardar los IDs de la página actual
        $this->currentPageIds = $admisiones->pluck('id')->toArray();
    
        return view('livewire.recibir', [
            'admisiones' => $admisiones,
        ]);
    }
    
    public function recibirAdmision()
    {
        if (!empty($this->selectedAdmisiones)) {
            // Cargar las admisiones seleccionadas en admissionData
            $admissions = Admision::whereIn('id', $this->selectedAdmisiones)->get();
    
            foreach ($admissions as $admission) {
                $this->admissionData[$admission->id] = [
                    'peso_ems' => $admission->peso_ems ?? '',
                    'observacion' => $admission->observacion ?? '',
                    'codigo' => $admission->codigo,
                ];
            }
    
            // Mostrar el modal
            $this->showModal = true;
    
        } else {
            session()->flash('error', 'Seleccione al menos una admisión.');
        }
    }
    public function saveAdmissionData()
    {
        // Validar los datos
        foreach ($this->admissionData as $id => $data) {
            $this->validate([
                'admissionData.' . $id . '.peso_ems' => 'nullable|numeric', // Cambiado a nullable
                'admissionData.' . $id . '.observacion' => 'nullable|string',
            ], [], [
                'admissionData.' . $id . '.peso_ems' => 'Peso EMS para admisión ' . $data['codigo'],
                'admissionData.' . $id . '.observacion' => 'Observación para admisión ' . $data['codigo'],
            ]);
        }
    
        // Actualizar las admisiones
        foreach ($this->admissionData as $id => $data) {
            Admision::where('id', $id)
                ->update([
                    'peso_ems' => $data['peso_ems'] !== '' ? $data['peso_ems'] : null, // Guardar como null si está vacío
                    'observacion' => $data['observacion'],
                    'estado' => 3,
                ]);
        }
    
        // Restablecer variables
        $this->selectedAdmisiones = [];
        $this->admissionData = [];
        $this->showModal = false;
        $this->selectAll = false;
    
        session()->flash('message', 'Las admisiones seleccionadas han sido recibidas.');
        $this->render(); // Refrescar la vista
    }
    
    
    public function removeAdmissionFromModal($id)
{
    // Verifica si el ID existe en el array y lo elimina
    if (isset($this->admissionData[$id])) {
        unset($this->admissionData[$id]);
    }

    // También elimínalo de la lista de seleccionados para que no se procese
    $this->selectedAdmisiones = array_filter($this->selectedAdmisiones, function ($selectedId) use ($id) {
        return $selectedId != $id;
    });
}

    public function updatedSelectAll($value)
    {
        $this->selectAllItems($value);
    }
    public function selectAllItems($value)
{
    if ($value) {
        // Selecciona todos los IDs visibles
        $this->selectedAdmisiones = $this->currentPageIds;
    } else {
        // Deselecciona todos los IDs
        $this->selectedAdmisiones = [];
    }
}
}
