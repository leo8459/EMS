<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tarifa;

class Tarifas extends Component
{
    use WithPagination;

    // Campos de la tabla 'tarifas'
    public $servicio;
    public $peso_min;
    public $peso_max;
    public $ems_local_cobertura_1;
    public $ems_local_cobertura_2;
    public $ems_local_cobertura_3;
    public $ems_local_cobertura_4;
    public $ems_nacional;
    public $destino_1;
    public $destino_2;
    public $destino_3;

   // Identificador del registro
   public $tarifaId;

   // Propiedades adicionales
   public $showModal = false;
   public $isEdit = false;
   public $perPage = 10;
   public $searchTerm;

    // Renderizar la vista con los datos
    public function render()
    {
        $admisiones = Tarifa::where('servicio', 'like', '%' . $this->searchTerm . '%')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.tarifas', ['admisiones' => $admisiones]);
    }

    // Métodos para manejar el modal y CRUD
    public function create()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $tarifa = Tarifa::findOrFail($id);
    
        // Llenar las propiedades con los datos del registro
        $this->fill($tarifa->toArray());
        $this->tarifaId = $tarifa->id;
    
        $this->isEdit = true;
        $this->showModal = true;
    }
    

    public function store()
    {
        $this->validate();
        Tarifa::create($this->validate());
        session()->flash('message', 'Tarifa creada exitosamente.');
        $this->closeModal();
    }
    public function update()
    {
        $this->validate();
    
        // Buscar el registro por el ID y actualizarlo
        $tarifa = Tarifa::findOrFail($this->tarifaId);
        $tarifa->update($this->validate());
    
        session()->flash('message', 'Tarifa actualizada exitosamente.');
        $this->closeModal();
    }
    

    public function delete($id)
    {
        Tarifa::findOrFail($id)->delete();
        session()->flash('message', 'Tarifa eliminada exitosamente.');
    }

    protected function resetInputFields()
    {
        $this->servicio = null;
        $this->peso_min = null;
        $this->peso_max = null;
        $this->ems_local_cobertura_1 = null;
        $this->ems_local_cobertura_2 = null;
        $this->ems_local_cobertura_3 = null;
        $this->ems_local_cobertura_4 = null;
        $this->ems_nacional = null;
        $this->destino_1 = null;
        $this->destino_2 = null;
        $this->destino_3 = null;
    }

    protected function rules()
    {
        return [
            'servicio' => 'required|string|max:255',
            'peso_min' => 'required|numeric|min:0',
            'peso_max' => 'required|numeric|min:0',
            'ems_local_cobertura_1' => 'nullable|integer',
            'ems_local_cobertura_2' => 'nullable|integer',
            'ems_local_cobertura_3' => 'nullable|integer',
            'ems_local_cobertura_4' => 'nullable|integer',
            'ems_nacional' => 'nullable|integer',
            'destino_1' => 'nullable|integer',
            'destino_2' => 'nullable|integer',
            'destino_3' => 'nullable|integer',
        ];
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
    }
}
