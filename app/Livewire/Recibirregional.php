<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Illuminate\Support\Facades\Auth;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento

class Recibirregional extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $perPage = 10;
    public $selectedAdmisiones = [];
    public $showModal = false;
    public $pesoEms, $pesoRegional, $observacion;
    public $selectedAdmisionesData = []; // Array para almacenar los datos seleccionados
    public $selectAll = false;



    public function render()
    {
        $userCity = Auth::user()->city;
    
        // Obtener los datos filtrados
        $admisiones = Admision::query()
            ->when($this->searchTerm, function ($query) {
                $query->where('codigo', 'like', '%' . $this->searchTerm . '%');
            })
            ->where(function ($query) use ($userCity) {
                $query->where('estado', 6)
                    ->where(function ($subQuery) use ($userCity) {
                        $subQuery->where('reencaminamiento', $userCity) // Si el reencaminamiento coincide con la ciudad del usuario
                            ->orWhereNull('reencaminamiento') // Si no hay reencaminamiento
                            ->where('ciudad', $userCity);    // Usar la ciudad
                    })
                    ->orWhere(function ($subQuery) use ($userCity) {
                        $subQuery->where('estado', 8)
                            ->where('reencaminamiento', $userCity); // Estado 8: Reencaminamiento coincide con la ciudad
                    });
            })
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);
    
        // Si hay un término de búsqueda, acumular las selecciones
        if (!empty($this->searchTerm)) {
            $newSelections = $admisiones->pluck('id')->toArray();
            $this->selectedAdmisiones = array_unique(array_merge($this->selectedAdmisiones, $newSelections));
    
            // Limpiar el campo de búsqueda
            $this->searchTerm = '';
        }
    
        return view('livewire.recibirregional', [
            'admisiones' => $admisiones,
        ]);
    }
    

    
    
    



public function openModal()
{
    if (empty($this->selectedAdmisiones)) {
        session()->flash('error', 'Debe seleccionar al menos un envío.');
        return;
    }

    // Cargar los datos de las admisiones seleccionadas
    $this->selectedAdmisionesData = Admision::whereIn('id', $this->selectedAdmisiones)
        ->get()
        ->map(function ($admision) {
            return [
                'id' => $admision->id, // Asegúrate de incluir el ID
                'codigo' => $admision->codigo,
                'peso_ems' => $admision->peso_ems ?: $admision->peso,
                'peso_regional' => $admision->peso_regional,
                'observacion' => $admision->observacion,
            ];
        })->toArray();

    $this->showModal = true;
}

    

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['pesoEms', 'pesoRegional', 'observacion']);
    }

    public function recibirEnvios()
    {
        foreach ($this->selectedAdmisionesData as $data) {
            if (!isset($data['id'])) {
                continue; // Saltar si no hay 'id'
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
    
        // Generar PDF
        $admisiones = Admision::whereIn('id', array_column($this->selectedAdmisionesData, 'id'))->get();
        $pdf = \PDF::loadView('pdfs.recibidosregional', compact('admisiones'));
    
        // Descargar PDF
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'admisiones_recibidas.pdf');
    }
    
    
    
    

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Seleccionar todas las admisiones visibles (en la página actual)
            $this->selectedAdmisiones = Admision::query()
                ->where(function ($query) {
                    $userCity = Auth::user()->city;
    
                    $query->where('estado', 6)
                        ->where(function ($subQuery) use ($userCity) {
                            $subQuery->where('reencaminamiento', $userCity) // Si el reencaminamiento coincide con la ciudad del usuario
                                ->orWhereNull('reencaminamiento') // Si no hay reencaminamiento
                                ->where('ciudad', $userCity);    // Usar la ciudad
                        });
                })
                ->orWhere(function ($query) {
                    $userCity = Auth::user()->city;
    
                    $query->where('estado', 8)
                        ->where('reencaminamiento', $userCity); // Estado 8: Reencaminamiento coincide con la ciudad
                })
                ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Aplicar búsqueda
                ->pluck('id') // Obtener los IDs
                ->toArray();
        } else {
            // Deseleccionar todo
            $this->selectedAdmisiones = [];
        }
    }
    
    public function generatePDF(Request $request)
    {
        $admisiones = Admision::whereIn('id', $request->selectedAdmisiones)->get();
    
        $pdf = \PDF::loadView('pdfs.recibidosregional', compact('admisiones'));
        return $pdf->download('admisiones_recibidas_regional.pdf');
    }
    


}
