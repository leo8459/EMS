<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Eventos;
use App\Models\Historico;
use Illuminate\Support\Facades\DB;

class Recibir extends Component
{
    use WithPagination;

    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 1000;
    public $admisionId;
    public $selectedAdmisiones = [];
    public $selectAll = false;
    public $showModal = false;
    public $admissionData = [];
    public $startDate;
    public $endDate;
    public $selectedDepartment;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $userCity = Auth::user()->city;

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

    /**
     * Recibir directamente al presionar ENTER en el buscador.
     * - Prioriza coincidencia exacta por código.
     * - Si no hay exacta y sólo hay 1 por LIKE, recibe ese único.
     * - Si hay múltiples por LIKE, muestra alerta de ambigüedad (no procesa).
     * - NO genera PDF; muestra alerta "Paquete recibido: CODIGO".
     */
    public function recibirPorBusqueda()
    {
        $term = trim((string) $this->searchTerm);
        if ($term === '') {
            session()->flash('error', 'Ingrese un código para buscar.');
            return;
        }

        $userCity = Auth::user()->city;

        // 1) Coincidencia exacta
        $exactMatches = Admision::where('origen', $userCity)
            ->where('estado', 2)
            ->where('codigo', $term)
            ->get();

        // 2) Si no hay exacta, buscar por LIKE y limitar a 10 para seguridad
        $likeMatches = collect();
        if ($exactMatches->isEmpty()) {
            $likeMatches = Admision::where('origen', $userCity)
                ->where('estado', 2)
                ->where('codigo', 'like', '%' . $term . '%')
                ->orderBy('fecha', 'desc')
                ->limit(10)
                ->get();

            if ($likeMatches->count() > 1) {
                session()->flash('error', 'La búsqueda es ambigua: se encontraron varios códigos. Especifique el código exacto.');
                return;
            }
        }

        // Determinar conjunto a procesar
        $toProcess = $exactMatches->isNotEmpty() ? $exactMatches : $likeMatches;

        if ($toProcess->isEmpty()) {
            session()->flash('error', 'No se encontraron admisiones con estado 2 para el código ingresado en su ciudad.');
            return;
        }

        // Procesar recepción sin PDF
        $codigosRecibidos = [];
        DB::beginTransaction();
        try {
            foreach ($toProcess as $admision) {
                // Evitar duplicar evento "Recibir"
                $exists = Eventos::where('codigo', $admision->codigo)
                    ->where('accion', 'Recibir')
                    ->exists();

                if (!$exists) {
                    Eventos::create([
                        'accion'           => 'Recibir',
                        'descripcion'      => 'La admisión fue recibida.',
                        'codigo'           => $admision->codigo,
                        'user_id'          => Auth::id(),
                        'origen'           => $admision->origen ?? 'No especificado',
                        'destino'          => $admision->reencaminamiento ?? $admision->ciudad ?? 'No especificado',
                        'cantidad'         => $admision->cantidad ?? 0,
                        'peso'             => $admision->peso_ems ?? $admision->peso ?? 0.0,
                        'observacion'      => $admision->observacion ?? 'Sin observación',
                        'fecha_recibido'   => now(),
                    ]);

                    Historico::create([
                        'numero_guia'              => $admision->codigo,
                        'fecha_actualizacion'      => now(),
                        'id_estado_actualizacion'  => 4,
                        'estado_actualizacion'     => ' "Operador" en posesión del envío',
                    ]);
                }

                // Cambiar estado a 3 (recibido)
                $admision->update([
                    'estado'      => 3,
                    'peso_ems'    => $admision->peso_ems ?? $admision->peso ?? null,
                    'observacion' => $admision->observacion ?? 'Sin observación',
                ]);

                $codigosRecibidos[] = $admision->codigo;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Error al recibir por búsqueda: ' . $e->getMessage());
            return;
        }

        // Limpiar búsqueda y mostrar alerta con código(s)
        $this->searchTerm = '';

        if (count($codigosRecibidos) === 1) {
            session()->flash('message', 'Paquete recibido: ' . $codigosRecibidos[0]);
        } else {
            session()->flash('message', 'Paquetes recibidos: ' . implode(', ', $codigosRecibidos));
        }

        // Livewire re-renderiza el componente; no recargar la página para conservar la alerta
    }

    public function recibirAdmision()
    {
        if (!empty($this->selectedAdmisiones)) {
            $admissions = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            foreach ($admissions as $admission) {
                $this->admissionData[$admission->id] = [
                    'peso_ems'    => $admission->peso_ems ?? '',
                    'observacion' => $admission->observacion ?? '',
                    'codigo'      => $admission->codigo,
                ];
            }

            $this->showModal = true;
            session()->flash('message', 'Las admisiones seleccionadas fueron cargadas para recepción.');
        } else {
            session()->flash('error', 'Seleccione al menos una admisión.');
        }
    }

    public function saveAdmissionData()
    {
        $admisionesProcesadas = [];

        foreach ($this->admissionData as $id => $data) {
            $this->validate([
                'admissionData.' . $id . '.peso_ems'    => 'nullable|numeric',
                'admissionData.' . $id . '.observacion' => 'nullable|string',
            ]);

            $admision = Admision::find($id);

            if ($admision) {
                $exists = Eventos::where('codigo', $admision->codigo)
                    ->where('accion', 'Recibir')
                    ->exists();

                if (!$exists) {
                    Eventos::create([
                        'accion'         => 'Recibir',
                        'descripcion'    => 'La admisión fue recibida.',
                        'codigo'         => $admision->codigo,
                        'user_id'        => Auth::id(),
                        'origen'         => $admision->origen ?? 'No especificado',
                        'destino'        => $admision->reencaminamiento ?? $admision->ciudad ?? 'No especificado',
                        'cantidad'       => $admision->cantidad ?? 0,
                        'peso'           => $admision->peso_ems ?? $admision->peso ?? 0.0,
                        'observacion'    => $data['observacion'] ?? 'Sin observación',
                        'fecha_recibido' => now(),
                    ]);

                    Historico::create([
                        'numero_guia'             => $admision->codigo,
                        'fecha_actualizacion'     => now(),
                        'id_estado_actualizacion' => 4,
                        'estado_actualizacion'    => ' "Operador" en posesión del envío',
                    ]);
                }

                $admision->update([
                    'peso_ems'    => $data['peso_ems'] !== '' ? $data['peso_ems'] : null,
                    'observacion' => $data['observacion'],
                    'estado'      => 3,
                ]);

                $admisionesProcesadas[] = $admision;
            }
        }

        // Mostrar alerta simple en vez de PDF
        if (!empty($admisionesProcesadas)) {
            $codes = collect($admisionesProcesadas)->pluck('codigo')->implode(', ');
            session()->flash('message', 'Paquetes recibidos: ' . $codes);
        } else {
            session()->flash('message', 'Las admisiones seleccionadas han sido procesadas.');
        }

        $this->reset(['selectedAdmisiones', 'admissionData', 'showModal']);
    }

    public function removeAdmissionFromModal($id)
    {
        if (isset($this->admissionData[$id])) {
            unset($this->admissionData[$id]);
        }
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
            $this->selectedAdmisiones = $this->currentPageIds;
        } else {
            $this->selectedAdmisiones = [];
        }
    }

    public function downloadReport()
    {
        // (Mantengo esta función por si la usas en otro lado; no se llama en el flujo por Enter)
        if (!$this->startDate || !$this->endDate) {
            session()->flash('error', 'Por favor seleccione un rango de fechas.');
            return;
        }

        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();

        $query = Eventos::where('accion', 'Recibir')
            ->whereBetween('created_at', [$start, $end]);

        if ($this->selectedDepartment) {
            $query->where('origen', $this->selectedDepartment);
        }

        $eventos = $query->get();

        if ($eventos->isEmpty()) {
            session()->flash('error', 'No se encontraron registros en este rango de fechas.');
            return;
        }

        // Si quieres desactivar completamente la descarga aquí, comenta lo siguiente y deja un mensaje.
        $pdf = \PDF::loadView('pdfs.recibir2', ['admisiones' => $eventos]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'reporte_admisiones_recibidas_' . now()->format('Ymd_His') . '.pdf');
    }

    public function recibirHoy()
    {
        $hoy = Carbon::today();

        $admisionesHoy = Admision::whereDate('fecha', $hoy)
            ->where('estado', 2)
            ->get();

        if ($admisionesHoy->isEmpty()) {
            session()->flash('error', 'No hay admisiones generadas el día de hoy.');
            return;
        }

        foreach ($admisionesHoy as $admission) {
            $this->admissionData[$admission->id] = [
                'peso_ems'    => $admission->peso_ems ?? '',
                'observacion' => $admission->observacion ?? '',
                'codigo'      => $admission->codigo,
            ];
        }

        $this->selectedAdmisiones = $admisionesHoy->pluck('id')->toArray();
        $this->showModal = true;

        session()->flash('message', 'Las admisiones generadas hoy han sido cargadas en el modal.');
    }

    // Dejamos estos helpers por si decides seguir usando reportes en otros flujos
    public function generateTodayReport($admisiones) {}
    public function generateReportFromAdmisiones($admisiones) {}
}
