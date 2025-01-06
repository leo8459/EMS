<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;
use App\Models\Eventos;
use Illuminate\Http\Request;

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



    public function render()
    {
        $userCity = Auth::user()->city;

        // Consulta base para las admisiones
        $admisiones = Admision::query()
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
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        
            $notificacionesDañadas = Admision::where('notificacion', 'DAÑADO')
            ->whereIn('estado', [7, 3, 10]) // Agregando los estados específicos
            ->where('origen', $userCity)
            ->whereNotNull('notificacion') // Verificando que no sea nulo
            ->get();
        
        if ($notificacionesDañadas->isEmpty()) {
            toastr()->error('No se encontraron admisiones con notificaciones de tipo "DAÑADO".');
        } else {
            $detalleNotificaciones = $notificacionesDañadas->pluck('codigo')->implode(', ');
            toastr()->error('Se encontraron admisiones con notificaciones de tipo "DAÑADO" en los siguientes registros: ' . $detalleNotificaciones);
        }
            $notificacionesMalencaminadas = Admision::where('notificacion', 'MALENCAMINADO')
            ->whereIn('estado', [7, 3, 10]) // Agregando los estados
            ->where('origen', $userCity)
            ->whereNotNull('notificacion') // Verificando que no sea nulo
            ->get();
        
        if ($notificacionesMalencaminadas->isEmpty()) {
            toastr()->error('No se encontraron notificaciones mal encaminadas con los criterios especificados.');
        } else {
            $detalleNotificaciones = $notificacionesMalencaminadas->pluck('codigo')->implode(', ');
            toastr()->error('Se encontraron notificaciones mal encaminadas en las siguientes admisiones: ' . $detalleNotificaciones);
        }
       

        $notificacionesFaltantes = Admision::where('notificacion', 'FALTANTE')
    ->whereIn('estado', [7, 3, 10]) // Agregando los estados específicos
    ->where('origen', $userCity)
    ->whereNotNull('notificacion') // Verificando que no sea nulo
    ->get();

if ($notificacionesFaltantes->isEmpty()) {
    toastr()->error('No se encontraron admisiones con notificaciones de tipo "FALTANTE".');
} else {
    $detalleNotificaciones = $notificacionesFaltantes->pluck('codigo')->implode(', ');
    toastr()->error('Se encontraron admisiones con notificaciones de tipo "FALTANTE" en los siguientes registros: ' . $detalleNotificaciones);
}

$notificacionesSobrantes = Admision::where('notificacion', 'SOBRANTE')
    ->whereIn('estado', [7, 3, 10]) // Agregando los estados específicos
    ->where('origen', $userCity)
    ->whereNotNull('notificacion') // Verificando que no sea nulo
    ->get();

if ($notificacionesSobrantes->isEmpty()) {
    toastr()->error('No se encontraron admisiones con notificaciones de tipo "SOBRANTE".');
} else {
    $detalleNotificaciones = $notificacionesSobrantes->pluck('codigo')->implode(', ');
    toastr()->error('Se encontraron admisiones con notificaciones de tipo "SOBRANTE" en los siguientes registros: ' . $detalleNotificaciones);
}

        return view('livewire.recibirregional', [
            'admisiones' => $admisiones,
            'notificacionesDañadas' => $notificacionesDañadas,
            'notificacionesMalencaminadas' => $notificacionesMalencaminadas,
        ]);
    }



    /**
     * Método para el botón "Buscar" 
     * (no hace nada especial más que forzar un render 
     *  cuando cambie searchTerm)
     */
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
                    'estado' => 7,
                ]);

                Eventos::create([
                    'accion' => 'Recibir Regional',
                    'descripcion' => 'Recepción de admisión desde la regional.',
                    'codigo' => $admision->codigo,
                    'user_id' => auth()->id(),
                ]);
            }
        }

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
}
