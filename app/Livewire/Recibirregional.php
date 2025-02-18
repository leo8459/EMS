<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;
use App\Models\Eventos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // Asegúrate de incluir esto
use Carbon\Carbon;
use App\Models\Historico; // Asegúrate de importar el modelo Evento
use Illuminate\Support\Facades\Http;

class Recibirregional extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $perPage = 10;
    public $selectedAdmisiones = [];
    public $showModal = false;
    public $pesoEms, $pesoRegional, $observacion;
    public $selectedAdmisionesData = [];
    public $selectAll = false;
    public $damagedAdmisiones = [];
    public $misroutedAdmisiones = [];
    public $startDate; // Agregado: para almacenar la fecha de inicio
    public $endDate; // Agregado: para almacenar la fecha de fin
    public $selectedDepartment; // Agregado: para el filtro de departamento
    public $showNotifications = true; // Nuevo: controla si se muestran las alertas
    public $solicitudesExternas = [];
    public $selectedSolicitudesExternas = [];
    public $selectedSolicitudesExternasData = [];


    public function buscar()
    {
        if (!empty($this->searchTerm)) {
            // 1) Filtrar Admisiones (internas) en la BD
            $filteredAdmisiones = Admision::where('codigo', 'like', '%' . $this->searchTerm . '%')
                ->where('estado', 6)
                ->pluck('id')
                ->toArray();

            // Unir con lo ya seleccionado (sin duplicar)
            $this->selectedAdmisiones = array_unique(
                array_merge($this->selectedAdmisiones, $filteredAdmisiones)
            );

            // 2) Filtrar en las "solicitudesExternas" (array) 
            //    Usamos EXACT MATCH, pero podrías hacer stripos() 
            //    para coincidencia parcial
            $filteredExternas = collect($this->solicitudesExternas)->filter(function ($solicitud) {
                return isset($solicitud['guia']) 
                       && ($solicitud['guia'] === $this->searchTerm);
            });

            // Unir con lo ya seleccionado
            $this->selectedSolicitudesExternas = array_unique(
                array_merge(
                    $this->selectedSolicitudesExternas, 
                    $filteredExternas->pluck('guia')->toArray()
                )
            );
        }

        // 3) Limpia el searchTerm
        $this->searchTerm = '';
    }
    public function render()
{
    $userCity = Auth::user()->city;

    // 1) Filtrar admisiones internas en BD
    $admisiones = Admision::query()
        ->when($this->searchTerm, function ($query) {
            $query->where('codigo', 'like', '%' . $this->searchTerm . '%');
        })
        ->where('estado', 6)
        ->where(function ($query) use ($userCity) {
            $query->where('reencaminamiento', $userCity)
                  ->orWhereNull('reencaminamiento')
                  ->where('ciudad', $userCity);
        })
        ->orderBy('fecha', 'desc')
        ->paginate($this->perPage);

    // 2) Filtrar solicitudes externas en array
    $filteredExternas = collect($this->solicitudesExternas)->filter(function ($solicitud) {
        return empty($this->searchTerm) || (isset($solicitud['guia']) && $solicitud['guia'] === $this->searchTerm);
    });

    // Si no hay coincidencias exactas, ocultar la tabla
    $showExternalTable = $filteredExternas->isNotEmpty();

    return view('livewire.recibirregional', [
        'admisiones'          => $admisiones,
        'solicitudesExternas' => $filteredExternas->values()->toArray(),
        'showExternalTable'   => $showExternalTable,
    ]);
}



    

    private function handleNotifications($userCity)
    {
        $types = ['DAÑADO', 'MALENCAMINADO', 'FALTANTE', 'SOBRANTE'];
        $hasNotifications = false; // Indicador de notificaciones
    
        foreach ($types as $type) {
            $count = Admision::where('notificacion', $type)
                ->whereIn('estado', [7, 3, 10])
                ->where('origen', $userCity)
                ->whereNotNull('notificacion')
                ->count(); // Contamos cuántos hay en total de este tipo
    
            if ($count > 0) {
                $hasNotifications = true; // Marca que hay notificaciones
                toastr()->error("Se encontraron $count admisiones con notificación de tipo \"$type\".");
            }
        }
    }
    
    



    /**
     * Método para el botón "Buscar" 
     * (no hace nada especial más que forzar un render 
     *  cuando cambie searchTerm)
     */
    public function mount()
    {
        // $response = Http::get('http://127.0.0.1:9000/api/ems/estado/8');
        $response = Http::get('http://172.65.10.52:8450/api/ems/estado/8');

        if ($response->successful()) {
            $this->solicitudesExternas = $response->json();
        } else {
            $this->solicitudesExternas = [];
        }

        $this->startDate = null;
        $this->endDate = null;
        $this->selectedDepartment = '';
        $this->showNotifications = true;
    }
    

    
    


    /**
     * Se dispara cuando cambia el valor de $selectAll.
     * Si $selectAll es true, selecciona todos los registros
     * según la consulta actual.
     */
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Seleccionar todos (filtrados por la ciudad y el estado)
            $userCity = Auth::user()->city;

            $visibleAdmisiones = Admision::query()
                ->when($this->searchTerm, function ($query) {
                    $query->where('codigo', 'like', '%' . $this->searchTerm . '%');
                })
                ->where(function ($query) use ($userCity) {
                    $query->where('estado', 6)
                        ->where(function ($subQuery) use ($userCity) {
                            $subQuery->where('reencaminamiento', $userCity)
                                ->orWhereNull('reencaminamiento')
                                ->where('ciudad', $userCity);
                        })
                        ->orWhere(function ($subQuery) use ($userCity) {
                            $subQuery->where('estado', 8)
                                ->where('reencaminamiento', $userCity);
                        });
                })
                ->pluck('id')
                ->toArray();

            $this->selectedAdmisiones = $visibleAdmisiones;
        } else {
            // Deseleccionar todo
            $this->selectedAdmisiones = [];
        }
    }

    /**
     * Abre el modal de "Recibir envíos" si hay admisiones seleccionadas,
     * cargando además los datos en $selectedAdmisionesData.
     */
    public function openModal()
    {
        // Al menos uno de los dos tipos de selección debe estar presente
        if (empty($this->selectedAdmisiones) && empty($this->selectedSolicitudesExternas)) {
            session()->flash('error', 'Debe seleccionar al menos un envío (interno o externo).');
            return;
        }

        // 1) Admisiones internas
        $this->selectedAdmisionesData = Admision::whereIn('id', $this->selectedAdmisiones)
            ->get()
            ->map(function ($admision) {
                return [
                    'id'            => $admision->id,
                    'codigo'        => $admision->codigo,
                    'peso_ems'      => $admision->peso_ems ?: $admision->peso,
                    'peso_regional' => $admision->peso_regional,
                    'observacion'   => $admision->observacion,
                    'notificacion'  => null,
                ];
            })
            ->toArray();

        // 2) Solicitudes externas
        $this->selectedSolicitudesExternasData = [];
        foreach ($this->solicitudesExternas as $solicitud) {
            if (in_array($solicitud['guia'], $this->selectedSolicitudesExternas)) {
                // Añadir campos según necesites
                $this->selectedSolicitudesExternasData[] = [
                    'guia'         => $solicitud['guia'],
                    'peso_o'       => $solicitud['peso_o'] ?? 0,
                    'observacion'  => '',
                    'notificacion' => null,
                ];
            }
        }

        // Mostrar modal
        $this->showModal = true;
    }

    /**
     * Cierra el modal y resetea pesos/observación 
     * (pero no borra la selección, para que sea persistente).
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['pesoEms', 'pesoRegional', 'observacion']);
    }

    /**
     * Guarda los cambios de peso/observación en cada admisión 
     * y cambia el estado a 7 (Recibido).
     * Además, genera un PDF con los datos.
     */
    public function recibirEnvios()
    {
        foreach ($this->selectedAdmisionesData as $data) {
            if (!isset($data['id'])) {
                continue;
            }
    
            $admision = Admision::find($data['id']);
            if ($admision) {
                $pesoRegional = $data['peso_regional'] !== null ? number_format((float) $data['peso_regional'], 3, '.', '') : null;
                $observacion = $data['observacion'] ?? '';
    
                // **Actualizar la admisión en la base de datos**
                $admision->update([
                    'peso_regional' => $pesoRegional,
                    'observacion' => $observacion,
                    'estado' => 10, // ✅ Cambia el estado a 10
                ]);
    
                // **Enviar actualización del peso y observación a la API**
                $response = Http::put('http://172.65.10.52:8450/api/solicitudes/estado', [
                    'guia' => (string) $admision->codigo, // ✅ Asegurar que se envía como string
                    'peso_r' => (string) $pesoRegional, // ✅ Enviar como string
                    'observacion' => (string) $observacion, // ✅ Enviar como string
                    'estado' => 10, // ✅ Mantener el cambio de estado
                ]);
    
                // **Depuración en el log de Laravel**
                \Log::info("Solicitud enviada a la API para la admisión: {$admision->codigo}", [
                    'peso_r' => $pesoRegional,
                    'observacion' => $observacion,
                    'estado' => 10,
                    'response_status' => $response->status(),
                    'response_body' => $response->json(),
                ]);
    
                // **Registrar el evento en la base de datos**
                Eventos::create([
                    'accion' => 'Recibir Regional',
                    'descripcion' => 'Recepción de admisión desde la regional.',
                    'codigo' => $admision->codigo,
                    'user_id' => auth()->id(),
                    'origen' => $admision->origen ?? 'No especificado',
                    'destino' => $admision->reencaminamiento ?? $admision->ciudad ?? 'No especificado',
                    'cantidad' => $admision->cantidad ?? 0,
                    'peso' => $pesoRegional,
                    'observacion' => $observacion ?? 'Sin observación',
                    'fecha_recibido' => now(),
                ]);
            }
        }
    
        // **Actualizar también las solicitudes externas**
        foreach ($this->selectedSolicitudesExternasData as $data) {
            $pesoExterno = isset($data['peso_o']) ? number_format((float) $data['peso_o'], 3, '.', '') : null;
            $observacionExterna = $data['observacion'] ?? '';
    
            $response = Http::put('http://172.65.10.52:8450/api/solicitudes/estado', [
                'guia' => (string) $data['guia'], // ✅ Asegurar que se envía como string
                'peso_r' => (string) $pesoExterno, // ✅ Enviar como string
                'observacion' => (string) $observacionExterna, // ✅ Enviar como string
                'estado' => 10, // ✅ Mantener el cambio de estado
            ]);
    
            // **Depuración en el log de Laravel**
            \Log::info("Solicitud enviada a la API para solicitud externa: {$data['guia']}", [
                'peso_r' => $pesoExterno,
                'observacion' => $observacionExterna,
                'estado' => 10,
                'response_status' => $response->status(),
                'response_body' => $response->json(),
            ]);
        }
    
        // **Cerrar modal y refrescar la página**
        $this->dispatch('reload-page');
        $this->closeModal();
    }
    
    

    

    /**
     * Ejemplo de método para descargar PDF sin el modal 
     * (opcional, si se usa en otra parte).
     */
    public function generatePDF(Request $request)
    {
        $admisiones = Admision::whereIn('id', $request->selectedAdmisiones)->get();
        $pdf = \PDF::loadView('pdfs.recibidosregional', compact('admisiones'));
        return $pdf->download('admisiones_recibidas_regional.pdf');
    }


    public function downloadReport2()
{
    if (!$this->startDate || !$this->endDate) {
        session()->flash('error', 'Por favor seleccione un rango de fechas.');
        return;
    }

    // Convertir las fechas al inicio y fin del día
    $start = Carbon::parse($this->startDate)->startOfDay(); // Inicio del día
    $end = Carbon::parse($this->endDate)->endOfDay(); // Fin del día (23:59:59)

    // Filtrar eventos por fechas
    $query = \App\Models\Eventos::where('accion', 'Recibir Regional')
        ->whereBetween('created_at', [$start, $end]);

    if ($this->selectedDepartment) {
        $query->where('destino', $this->selectedDepartment); // Filtrar por destino
    }

    $eventos = $query->get();

    if ($eventos->isEmpty()) {
        session()->flash('error', 'No se encontraron registros en este rango de fechas.');
        return;
    }

    // Generar el PDF con el diseño
    $pdf = \PDF::loadView('pdfs.recibidosregional2', ['admisiones' => $eventos]);

    return response()->streamDownload(function () use ($pdf) {
        echo $pdf->stream();
    }, 'reporte_admisiones_recibidas_' . now()->format('Ymd_His') . '.pdf');
}

    
}
