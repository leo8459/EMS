<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento



class Recibir extends Component
{
    use WithPagination;
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 1000;
    public $admisionId;
    public $selectedAdmisiones = []; // Para almacenar los IDs seleccionados
    public $selectAll = false; // Añadido para controlar el seleccionar todo
    public $showModal = false;
    public $admissionData = [];
    public $startDate;
    public $endDate;
    public $selectedDepartment; // Almacena el departamento seleccionado

    public function render()
    {
        $userCity = Auth::user()->city;

        // Filtrar y paginar los registros
        $query = Admision::where('origen', $userCity)
            ->where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->where('estado', 2);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('fecha', [$this->startDate, $this->endDate]);
        }

        $admisiones = $query->orderBy('fecha', 'desc')->paginate($this->perPage);

        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.recibir', [
            'admisiones' => $admisiones,
        ]);
    }

    public function recibirAdmision()
    {
        if (!empty($this->selectedAdmisiones)) {
            // Cargar las admisiones seleccionadas
            $admissions = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            foreach ($admissions as $admission) {
                $this->admissionData[$admission->id] = [
                    'peso_ems' => $admission->peso_ems ?? '',
                    'observacion' => $admission->observacion ?? '',
                    'codigo' => $admission->codigo,
                ];

                // Registrar evento por cada admisión recibida
                // Registrar evento por cada admisión recibida
                // foreach ($admissions as $admission) {
                //     Eventos::create([
                //         'accion' => 'Recibir',
                //         'descripcion' => 'La admisión fue recibida.',
                //         'codigo' => $admission->codigo,
                //         'user_id' => Auth::id(),
                //         'origen' => $admission->origen,
                //         'destino' => $admission->destino,
                //         'cantidad' => $admission->cantidad,
                //         'peso' => $admission->peso_ems ?? $admission->peso, // Usar el peso EMS si está disponible
                //         'observacion' => $admission->observacion ?? '',
                //         'fecha_recibido' => now(),
                //     ]);
                // }
            }

            // Mostrar el modal
            $this->showModal = true;

            session()->flash('message', 'Las admisiones seleccionadas fueron recibidas exitosamente.');
        } else {
            session()->flash('error', 'Seleccione al menos una admisión.');
        }
    }

    public function saveAdmissionData()
    {
        foreach ($this->admissionData as $id => $data) {
            $this->validate([
                'admissionData.' . $id . '.peso_ems' => 'nullable|numeric',
                'admissionData.' . $id . '.observacion' => 'nullable|string',
            ]);
    
            $admision = Admision::find($id);
    
            if ($admision) {
                // Verificar si ya existe un evento para esta admisión
                $exists = \App\Models\Eventos::where('codigo', $admision->codigo)
                    ->where('accion', 'Recibir')
                    ->exists();
    
                if (!$exists) {
                    // Guardar el evento con los detalles
                    Eventos::create([
                        'accion' => 'Recibir',
                        'descripcion' => 'La admisión fue recibida.',
                        'codigo' => $admision->codigo,
                        'user_id' => Auth::id(),
                        'origen' => $admision->origen ?? 'No especificado',
                        'destino' => $admision->reencaminamiento ?? $admision->ciudad ?? 'No especificado', // Primero reencaminamiento, luego ciudad
                        'cantidad' => $admision->cantidad ?? 0,
                        'peso' => $admision->peso_ems ?? $admision->peso ?? 0.0,
                        'observacion' => $admision->observacion ?? 'Sin observación',
                        'fecha_recibido' => now(),
                    ]);
                    
                }
    
                // Actualizar la admisión
                $admision->update([
                    'peso_ems' => $data['peso_ems'] !== '' ? $data['peso_ems'] : null,
                    'observacion' => $data['observacion'],
                    'estado' => 3,
                ]);
            }
        }
    
        session()->flash('message', 'Las admisiones seleccionadas han sido procesadas.');
    
        $this->reset(['selectedAdmisiones', 'admissionData', 'showModal']);
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


    public function downloadReport()
    {
        if (!$this->startDate || !$this->endDate) {
            session()->flash('error', 'Por favor seleccione un rango de fechas.');
            return;
        }
    
        // Convertir las fechas al inicio y fin del día
        $start = Carbon::parse($this->startDate)->startOfDay(); // Inicio del día (00:00:00)
        $end = Carbon::parse($this->endDate)->endOfDay(); // Fin del día (23:59:59)
    
        // Obtener eventos de tipo "Recibir" filtrados por fechas y departamento
        $query = \App\Models\Eventos::where('accion', 'Recibir')
            ->whereBetween('created_at', [$start, $end]);
    
        if ($this->selectedDepartment) {
            $query->where('origen', $this->selectedDepartment);
        }
    
        $eventos = $query->get();
    
        if ($eventos->isEmpty()) {
            session()->flash('error', 'No se encontraron registros en este rango de fechas.');
            return;
        }
    
        // Generar el PDF con el diseño
        $pdf = \PDF::loadView('pdfs.recibir2', ['admisiones' => $eventos]);
    
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'reporte_admisiones_recibidas_' . now()->format('Ymd_His') . '.pdf');
    }
    
}
