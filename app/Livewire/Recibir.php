<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento
use App\Models\Historico; // Asegúrate de importar el modelo Evento



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
        $admisionesProcesadas = [];

        foreach ($this->admissionData as $id => $data) {
            $this->validate([
                'admissionData.' . $id . '.peso_ems' => 'nullable|numeric',
                'admissionData.' . $id . '.observacion' => 'nullable|string',
            ]);

            $admision = Admision::find($id);

            if ($admision) {
                // Registrar el evento si no existe previamente
                $exists = \App\Models\Eventos::where('codigo', $admision->codigo)
                    ->where('accion', 'Recibir')
                    ->exists();

                if (!$exists) {
                    Eventos::create([
                        'accion' => 'Recibir',
                        'descripcion' => 'La admisión fue recibida.',
                        'codigo' => $admision->codigo,
                        'user_id' => Auth::id(),
                        'origen' => $admision->origen ?? 'No especificado',
                        'destino' => $admision->reencaminamiento ?? $admision->ciudad ?? 'No especificado',
                        'cantidad' => $admision->cantidad ?? 0,
                        'peso' => $admision->peso_ems ?? $admision->peso ?? 0.0,
                        'observacion' => $data['observacion'] ?? 'Sin observación', // Usa la observación del formulario
                        'fecha_recibido' => now(),
                    ]);
                    Historico::create([
                        'numero_guia' => $admision->codigo, // Asignar el código único de admisión al número de guía
                        'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
                        'id_estado_actualizacion' => 4, // Estado inicial: 1
                        'estado_actualizacion' => ' "Operador" en posesión del envío', // Descripción del estado
                    ]);
                }

                // Actualizar el estado y detalles de la admisión
                $admision->update([
                    'peso_ems' => $data['peso_ems'] !== '' ? $data['peso_ems'] : null,
                    'observacion' => $data['observacion'],
                    'estado' => 3, // Cambiar el estado solo al confirmar
                ]);

                $admisionesProcesadas[] = $admision;
            }
        }
        $this->dispatch('reload-page');

        // Descargar el reporte si se procesaron admisiones
        if (!empty($admisionesProcesadas)) {
            return $this->generateReportFromAdmisiones(collect($admisionesProcesadas));
        }

        session()->flash('message', 'Las admisiones seleccionadas han sido procesadas.');
        // Emitir evento para recargar la página
        // Resetear el estado del modal y los datos
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



    public function recibirHoy()
    {
        // Obtener la fecha de hoy
        $hoy = Carbon::today();

        // Buscar todas las admisiones con estado 2 creadas hoy
        $admisionesHoy = Admision::whereDate('fecha', $hoy)
            ->where('estado', 2)
            ->get();

        if ($admisionesHoy->isEmpty()) {
            session()->flash('error', 'No hay admisiones generadas el día de hoy.');
            return;
        }

        // Cargar las admisiones en el modal
        foreach ($admisionesHoy as $admission) {
            $this->admissionData[$admission->id] = [
                'peso_ems' => $admission->peso_ems ?? '',
                'observacion' => $admission->observacion ?? '',
                'codigo' => $admission->codigo,
            ];
        }

        // Marcar las admisiones de hoy como seleccionadas
        $this->selectedAdmisiones = $admisionesHoy->pluck('id')->toArray();

        // Mostrar el modal
        $this->showModal = true;
        if (!empty($this->admissionData)) {
            $this->generateTodayReport(collect($this->admissionData));
        }

        session()->flash('message', 'Las admisiones generadas hoy han sido cargadas en el modal.');
    }


    public function generateTodayReport($admisiones)
    {
        $hoy = Carbon::today()->format('Y-m-d');

        try {
            // Generar el PDF usando la vista
            $pdf = \PDF::loadView('pdfs.recibir2', ['admisiones' => $admisiones]);

            // Guardar el PDF en el servidor
            $filePath = storage_path('app/public/reportes/reporte_admisiones_' . $hoy . '.pdf');
            \Storage::put('public/reportes/reporte_admisiones_' . $hoy . '.pdf', $pdf->output());

            // También puedes devolver la descarga directa
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, 'reporte_admisiones_' . $hoy . '.pdf');
        } catch (\Exception $e) {
            // Manejar errores en caso de que falle la generación del PDF
        }
    }
    public function generateReportFromAdmisiones($admisiones)
    {
        try {
            // Generar el PDF con las admisiones procesadas
            $pdf = \PDF::loadView('pdfs.recibir', ['admisiones' => $admisiones]);

            // Devolver el PDF como una descarga
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, 'reporte_admisiones_' . now()->format('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            // Manejar errores si la generación del PDF falla
            session()->flash('error', 'Ocurrió un error al generar el reporte: ' . $e->getMessage());
            return;
        }
    }
}
