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

class Recibirregionaladmin extends Component
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


    public function render()
    {
        $userCity = Auth::user()->city;
    
        // Consulta base para las admisiones
        $admisiones = Admision::query()
            ->when($this->searchTerm, function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%');
            })
            // Filtra solo los que están en estado 6 o 8
            ->whereIn('estado', [6, 8])
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);
    
        // Mostrar notificaciones solo si $showNotifications es verdadero
        if ($this->showNotifications) {
            $this->handleNotifications($userCity);
            $this->showNotifications = false; // Desactiva las alertas para siguientes renders
        }
    
        return view('livewire.recibirregionaladmin', [
            'admisiones' => $admisiones,
        ]);
    }

    

    

    private function handleNotifications($userCity)
    {
        $types = ['DAÑADO', 'MALENCAMINADO', 'FALTANTE', 'SOBRANTE'];
        $hasNotifications = false; // Indicador de notificaciones
    
        foreach ($types as $type) {
            $notificaciones = Admision::where('notificacion', $type)
                ->whereIn('estado', [7, 3, 10])
                ->where('origen', $userCity)
                ->whereNotNull('notificacion')
                ->get();
    
            if ($notificaciones->isNotEmpty()) {
                $hasNotifications = true; // Marca que hay notificaciones
                $detalleNotificaciones = $notificaciones->pluck('codigo')->implode(', ');
                toastr()->error("Se encontraron admisiones con notificaciones de tipo \"$type\" en los siguientes registros: $detalleNotificaciones");
            }
        }
    
        // Si no hay notificaciones, evita mostrar mensajes
        if (!$hasNotifications) {
            // No se hace nada, ya que no hay advertencias que mostrar
        }
    }
    



    /**
     * Método para el botón "Buscar" 
     * (no hace nada especial más que forzar un render 
     *  cuando cambie searchTerm)
     */
    public function mount()
    {
        $this->startDate = null; // Fecha de inicio vacía
        $this->endDate = null;   // Fecha de fin vacía
        $this->selectedDepartment = ''; // Sin departamento seleccionado por defecto
        $this->showNotifications = true; // Muestra las alertas al cargar la vista

    }
    

    public function buscar()
    {
        // Dejar vacío o poner alguna lógica de validación
        // Al cambiar $searchTerm con wire:model.defer, Livewire
        // hará un refresco automático del render.
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
        if (empty($this->selectedAdmisiones)) {
            session()->flash('error', 'Debe seleccionar al menos un envío.');
            return;
        }

        $this->selectedAdmisionesData = Admision::whereIn('id', $this->selectedAdmisiones)
            ->get()
            ->map(function ($admision) {
                return [
                    'id' => $admision->id,
                    'codigo' => $admision->codigo,
                    'peso_ems' => $admision->peso_ems ?: $admision->peso,
                    'peso_regional' => $admision->peso_regional,
                    'observacion' => $admision->observacion,
                ];
            })->toArray();

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
                $admision->update([
                    'peso_ems' => $data['peso_ems'] ?? null,
                    'peso_regional' => $data['peso_regional'] ?? null,
                    'observacion' => $data['observacion'] ?? null,
                    'notificacion' => $data['notificacion'] ?? null, // Guardar notificación
                    'estado' => 7,
                ]);
    
                Eventos::create([
                   'accion' => 'Recibir Regional',
                    'descripcion' => 'Recepción de admisión desde la regional.',
                    'codigo' => $admision->codigo,
                    'user_id' => auth()->id(),
                    'origen' => $admision->origen ?? 'No especificado',
                    'destino' => $admision->reencaminamiento ?? $admision->ciudad ?? 'No especificado', // Primero reencaminamiento, luego ciudad
                    'cantidad' => $admision->cantidad ?? 0,
                    'peso' => $admision->peso_ems ?? $admision->peso ?? 0.0,
                    'observacion' => $admision->observacion ?? 'Sin observación',
                    'fecha_recibido' => now(),
                ]);
                Historico::create([
                    'numero_guia' => $admision->codigo, // Asignar el código único de admisión al número de guía
                    'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
                    'id_estado_actualizacion' => 4, // Estado inicial: 1
                    'estado_actualizacion' => ' "Operador" en posesión del envío', // Descripción del estado
                ]);
            }
        }
        $this->dispatch('reload-page');

        // Generar el PDF con todas las admisiones que se acaban de recibir
        $admisiones = Admision::whereIn('id', array_column($this->selectedAdmisionesData, 'id'))->get();
        $pdf = \PDF::loadView('pdfs.recibidosregional', compact('admisiones'));
    
        // Descargar PDF
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'admisiones_recibidas.pdf');
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
