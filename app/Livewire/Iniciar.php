<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Iniciar extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $origen, $fecha, $servicio, $tipo_correspondencia, $cantidad, $peso, $destino, $codigo, $precio, $numero_factura, $nombre_remitente, $nombre_envia, $carnet, $telefono_remitente, $nombre_destinatario, $telefono_destinatario, $direccion, $ciudad, $pais;

    protected $rules = [
        'origen' => 'required|string|max:255',
        'servicio' => 'required|string|max:255',
        'tipo_correspondencia' => 'required|string|max:255',
        'cantidad' => 'required|integer',
        'peso' => ['required', 'regex:/^[0-9]*[.,]?[0-9]+$/', 'min:0', 'max:100'],
        'destino' => 'required|string|max:255',
        'codigo' => 'required|string|unique:admisions,codigo',
        'numero_factura' => 'nullable|string',
        'nombre_remitente' => 'required|string|max:255',
        'nombre_envia' => 'required|string|max:255',
        'carnet' => 'required|string',
        'telefono_remitente' => 'required|string',
        'nombre_destinatario' => 'required|string|max:255',
        'telefono_destinatario' => 'required|string',
        'direccion' => 'required|string',
        'ciudad' => 'required|string',
        'pais' => 'required|string',
    ];

    public function mount()
    {
        $this->origen = Auth::user()->city; // Si tienes una ciudad por defecto
        $this->cantidad = 1; // Establece cantidad en 1
    }

    public function render()
    {
        $admisiones = Admision::where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->paginate($this->perPage);

        return view('livewire.iniciar', [
            'admisiones' => $admisiones,
        ]);
    }

    public function resetInputFields()
    {
        $this->admisionId = null;
        $this->servicio = '';
        $this->tipo_correspondencia = '';
        $this->cantidad = 1; // Restablece cantidad a 1
        $this->peso = '';
        $this->destino = '';
        $this->codigo = '';
        $this->precio = '';
        $this->numero_factura = '';
        $this->nombre_remitente = '';
        $this->nombre_envia = '';
        $this->carnet = '';
        $this->telefono_remitente = '';
        $this->nombre_destinatario = '';
        $this->telefono_destinatario = '';
        $this->direccion = '';
        $this->ciudad = '';
        $this->pais = '';
    }

    public function store()
    {
        $this->validate();
        $this->fecha = now();

        Admision::create([
            'origen' => $this->origen,
            'fecha' => $this->fecha,
            'servicio' => $this->servicio,
            'tipo_correspondencia' => $this->tipo_correspondencia,
            'cantidad' => $this->cantidad,
            'peso' => $this->peso,
            'destino' => $this->destino,
            'codigo' => $this->codigo,
            'precio' => $this->precio,
            'numero_factura' => $this->numero_factura,
            'nombre_remitente' => $this->nombre_remitente,
            'nombre_envia' => $this->nombre_envia,
            'carnet' => $this->carnet,
            'telefono_remitente' => $this->telefono_remitente,
            'nombre_destinatario' => $this->nombre_destinatario,
            'telefono_destinatario' => $this->telefono_destinatario,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'pais' => $this->pais,
        ]);

        session()->flash('message', 'Despacho creado exitosamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $admision = Admision::findOrFail($id);
        $this->admisionId = $admision->id;
        $this->fill($admision->toArray());
        $this->fecha = Carbon::parse($admision->fecha)->format('Y-m-d\TH:i');
    }

    public function update()
    {
        $this->validate();

        $admision = Admision::findOrFail($this->admisionId);
        $admision->update([
            'origen' => $this->origen,
            'fecha' => Carbon::parse($this->fecha)->format('Y-m-d H:i:s'),
            'servicio' => $this->servicio,
            'tipo_correspondencia' => $this->tipo_correspondencia,
            'cantidad' => $this->cantidad,
            'peso' => $this->peso,
            'destino' => $this->destino,
            'codigo' => $this->codigo,
            'precio' => $this->precio,
            'numero_factura' => $this->numero_factura,
            'nombre_remitente' => $this->nombre_remitente,
            'nombre_envia' => $this->nombre_envia,
            'carnet' => $this->carnet,
            'telefono_remitente' => $this->telefono_remitente,
            'nombre_destinatario' => $this->nombre_destinatario,
            'telefono_destinatario' => $this->telefono_destinatario,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'pais' => $this->pais,
        ]);

        session()->flash('message', 'Despacho actualizado exitosamente.');
        $this->resetInputFields();
    }

    public function delete($id)
    {
        Admision::findOrFail($id)->delete();
        session()->flash('message', 'Despacho eliminado exitosamente.');
    }

    public function getPriceByWeightAndDestination($peso, $destino)
    {
        $tarifas = [
            'NACIONAL' => [
                [0.001, 0.250, 10],
                [0.251, 0.500, 12],
                [0.501, 1, 17],
                [1.001, 2, 23],
                [2.001, 3, 28],
                [3.001, 4, 34],
                [4.001, 5, 41],
                [5.001, 6, 48],
                [6.001, 7, 54],
                [7.001, 8, 60],
                [8.001, 9, 68],
                [9.001, 10, 74],
                [10.001, 11, 81],
                [11.001, 12, 87],
                [12.001, 13, 94],
                [13.001, 14, 101],
                [14.001, 15, 107],
                [15.001, 16, 114],
                [16.001, 17, 121],
                [17.001, 18, 127],
                [18.001, 19, 134],
                [19.001, 20, 140],
            ],
            'CIUDADES INTERMEDIAS' => [
                [0.001, 0.250, 18],
                [0.251, 0.500, 20],
                [0.501, 1, 25],
                [1.001, 2, 29],
                [2.001, 3, 37],
                [3.001, 4, 44],
                [4.001, 5, 47],
                [5.001, 6, 56],
                [6.001, 7, 62],
                [7.001, 8, 70],
                [8.001, 9, 77],
                [9.001, 10, 83],
                [10.001, 11, 86],
                [11.001, 12, 96],
                [12.001, 13, 102],
                [13.001, 14, 109],
                [14.001, 15, 112],
                [15.001, 16, 123],
                [16.001, 17, 129],
                [17.001, 18, 135],
                [18.001, 19, 142],
                [19.001, 20, 150],
            ],
            'TRINIDAD COBIJA' => [
                [0.001, 0.250, 16],
                [0.251, 0.500, 24],
                [0.501, 1, 31],
                [1.001, 2, 47],
                [2.001, 3, 62],
                [3.001, 4, 78],
                [4.001, 5, 94],
                [5.001, 6, 109],
                [6.001, 7, 125],
                [7.001, 8, 140],
                [8.001, 9, 156],
                [9.001, 10, 171],
                [10.001, 11, 187],
                [11.001, 12, 203],
                [12.001, 13, 218],
                [13.001, 14, 234],
                [14.001, 15, 249],
                [15.001, 16, 265],
                [16.001, 17, 281],
                [17.001, 18, 296],
                [18.001, 19, 312],
                [19.001, 20, 327],
            ],
            'RIVERALTA GUAYARAMERIN' => [
                [0.001, 0.250, 21],
                [0.251, 0.500, 26],
                [0.501, 1, 42],
                [1.001, 2, 62],
                [2.001, 3, 83],
                [3.001, 4, 104],
                [4.001, 5, 125],
                [5.001, 6, 145],
                [6.001, 7, 166],
                [7.001, 8, 187],
                [8.001, 9, 208],
                [9.001, 10, 229],
                [10.001, 11, 249],
                [11.001, 12, 270],
                [12.001, 13, 291],
                [13.001, 14, 312],
                [14.001, 15, 332],
                [15.001, 16, 353],
                [16.001, 17, 374],
                [17.001, 18, 395],
                [18.001, 19, 416],
                [19.001, 20, 436],
            ],
            'EMS COBERTURA 1' => [
                [0.001, 0.250, 3],
                [0.251, 0.500, 6],
                [0.501, 1, 10],
                [1.001, 2, 17],
                [2.001, 3, 22],
                [3.001, 4, 24],
                [4.001, 5, 26],
                [5.001, 6, 28],
                [6.001, 7, 30],
                [7.001, 8, 32],
                [8.001, 9, 34],
                [9.001, 10, 36],
                [10.001, 11, 38],
                [11.001, 12, 41],
                [12.001, 13, 43],
                [13.001, 14, 45],
                [14.001, 15, 47],
                [15.001, 16, 49],
                [16.001, 17, 51],
                [17.001, 18, 53],
                [18.001, 19, 55],
                [19.001, 20, 57],
            ],
            'EMS COBERTURA 2' => [
                [0.001, 0.250, 4],
                [0.251, 0.500, 7],
                [0.501, 1, 11],
                [1.001, 2, 18],
                [2.001, 3, 24],
                [3.001, 4, 25],
                [4.001, 5, 27],
                [5.001, 6, 29],
                [6.001, 7, 31],
                [7.001, 8, 33],
                [8.001, 9, 35],
                [9.001, 10, 37],
                [10.001, 11, 39],
                [11.001, 12, 42],
                [12.001, 13, 44],
                [13.001, 14, 46],
                [14.001, 15, 48],
                [15.001, 16, 50],
                [16.001, 17, 52],
                [17.001, 18, 54],
                [18.001, 19, 56],
                [19.001, 20, 58],
            ],
            'EMS COBERTURA 3' => [
                [0.001, 0.250, 5],
                [0.251, 0.500, 8],
                [0.501, 1, 12],
                [1.001, 2, 19],
                [2.001, 3, 24],
                [3.001, 4, 26],
                [4.001, 5, 28],
                [5.001, 6, 30],
                [6.001, 7, 32],
                [7.001, 8, 34],
                [8.001, 9, 36],
                [9.001, 10, 38],
                [10.001, 11, 41],
                [11.001, 12, 43],
                [12.001, 13, 45],
                [13.001, 14, 47],
                [14.001, 15, 49],
                [15.001, 16, 51],
                [16.001, 17, 53],
                [17.001, 18, 55],
                [18.001, 19, 57],
                [19.001, 20, 59],
            ],
            'EMS COBERTURA 4' => [
                [0.001, 0.250, 6],
                [0.251, 0.500, 10],
                [0.501, 1, 15],
                [1.001, 2, 21],
                [2.001, 3, 26],
                [3.001, 4, 28],
                [4.001, 5, 30],
                [5.001, 6, 32],
                [6.001, 7, 34],
                [7.001, 8, 36],
                [8.001, 9, 38],
                [9.001, 10, 41],
                [10.001, 11, 43],
                [11.001, 12, 45],
                [12.001, 13, 47],
                [13.001, 14, 49],
                [14.001, 15, 51],
                [15.001, 16, 53],
                [16.001, 17, 55],
                [17.001, 18, 57],
                [18.001, 19, 59],
                [19.001, 20, 61],
            ],
        ];

        if (!isset($tarifas[$destino])) {
            return null;
        }

        foreach ($tarifas[$destino] as $rango) {
            [$minPeso, $maxPeso, $precio] = $rango;
            if ($peso >= $minPeso && $peso <= $maxPeso) {
                return $precio;
            }
        }

        return null;
    }

    public function updatedPeso($value)
    {
        $this->peso = $value;
        $this->updatePrice();
    }
    
    
    
    

    public function updatedDestino()
    {
        $this->updatePrice();
    }

    public function updatePrice()
{
    $peso = str_replace(',', '.', $this->peso);
    if (is_numeric($peso) && (float)$peso > 0 && !empty($this->destino)) {
        $this->precio = $this->getPriceByWeightAndDestination((float)$peso, $this->destino);
    } else {
        $this->precio = null;
    }
}

    
}
