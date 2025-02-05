<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento
use App\Models\Tarifa;
use Illuminate\Http\Request;
use App\Models\Historico;

class Iniciar extends Component
{
    use WithPagination;
    public $selectedAdmisiones = [];
    public $selectAll = false;
    public $currentPageIds = [];

    public $searchTerm = '';
    public $perPage = 10;
    public $admisionId;
    public $origen, $fecha, $servicio, $tipo_correspondencia, $cantidad, $peso, $destino, $codigo, $precio, $numero_factura, $nombre_remitente, $nombre_envia, $carnet, $telefono_remitente, $nombre_destinatario, $telefono_destinatario, $direccion, $ciudad, $pais, $provincia, $contenido;
    public $codigosHoy = []; // Almacena los códigos de los registros generados hoy
    public $showModalExpedicionHoy = false; // Controla la visibilidad del modal
    public $admisionesParaExpedicion = []; // Variable para almacenar admisiones seleccionadas

    protected function rules()
    {
        return [
            'origen' => 'required|string|max:255',
            'servicio' => 'required|string|max:255',
            'tipo_correspondencia' => 'required|string|max:255',
            'cantidad' => 'required|integer',
            'peso' => ['required', 'regex:/^[0-9]*[.,]?[0-9]+$/', 'min:0', 'max:100'],
            'destino' => 'required|string|max:255',
            // 'codigo' => 'string|unique:admisions,codigo' . ($this->admisionId ? ',' . $this->admisionId : ''),
            'numero_factura' => 'nullable|string',
            'nombre_remitente' => 'required|string|max:255',
            // 'nombre_envia' => 'required|string|max:255',
            'carnet' => 'required|string',
            'telefono_remitente' => 'required|string',
            'nombre_destinatario' => 'required|string|max:255',
            // 'telefono_destinatario' => 'required|string',
            'direccion' => 'required|string',
            'ciudad' => 'required|in:LA PAZ,POTOSI,ORURO,SANTA CRUZ,CHUQUISACA,COCHABAMBA,BENI,PANDO,TARIJA',
            'pais' => 'required|string',
            'contenido' => 'nullable|string|max:500', // Regla de validación

        ];
    }
    //rescatar datos 
    public function mount()
    {
        $this->origen = Auth::user()->city; // Si tienes una ciudad por defecto
        $this->ciudad = ""; // Cambia este valor si quieres que otra ciudad sea la predeterminada
        $this->cantidad = 1; // Establece cantidad en 1
    }
    //mostrar
    public function render()
    {
        // Filtrar y paginar los registros
        $admisiones = Admision::where('origen', $this->origen) // Filtrar por origen
            ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por término de búsqueda
            ->where('estado', 1) // Solo estado activo
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        // Almacena los IDs de la página actual
        $this->currentPageIds = $admisiones->pluck('id')->toArray();

        return view('livewire.iniciar', [
            'admisiones' => $admisiones,
        ]);
    }



    protected $listeners = ['resetFields' => 'resetInputFields'];

    public function resetInputFields()
    {
        $this->origen = '';
        $this->fecha = '';
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
        $this->provincia = '';
        $this->pais = '';
    }

    public function store()
    {
        // Validar los datos del formulario
        $this->validate();

        // Establecer la fecha actual
        $this->fecha = now();

        // Calcular el precio basado en el peso y el destino
        $this->updatePrice();

        // Generar el código dinámicamente
        $prefixes = [
            'EMS' => 'EN',
            'SUPEREXPRESS' => 'EX',
            // Agrega otros servicios y prefijos según sea necesario
        ];
        $prefix = isset($prefixes[$this->servicio]) ? $prefixes[$this->servicio] : 'XX';

        $cityCodes = [
            'LA PAZ' => '0',
            'COCHABAMBA' => '1',
            'SANTA CRUZ' => '2',
            'ORURO' => '3',
            'POTOSI' => '4',
            'CHUQUISACA' => '5',
            'TARIJA' => '6',
            'PANDO' => '7',
            'BENI' => '8',

        ];

        $city = Auth::user()->city;
        $cityCode = isset($cityCodes[$city]) ? $cityCodes[$city] : '0';

        // Obtener los últimos dos dígitos del año actual
        $yearSuffix = now()->format('y'); // Esto devuelve '25' para el año 2025

        // Obtener el número máximo utilizado para este servicio, ciudad y año
        $lastNumber = Admision::where('codigo', 'like', $prefix . $cityCode . $yearSuffix . '%')
            ->selectRaw("MAX(CAST(REGEXP_REPLACE(SUBSTRING(codigo FROM 6), '[^0-9]', '', 'g') AS INTEGER)) as max_number")
            ->value('max_number');

        $newNumber = $lastNumber ? $lastNumber + 1 : 1;
        $numberPart = str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        // Sufijo es 'BO'
        $suffix = 'BO';
        $this->codigo = $prefix . $cityCode . $yearSuffix . $numberPart . $suffix;

        // Crear el registro en la base de datos
        $admision = Admision::create([
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
            'provincia' => $this->provincia,
            'ciudad' => $this->ciudad,
            'pais' => $this->pais,
            'contenido' => $this->contenido,
            'estado' => 1,
            'user_id' => Auth::id(),
            'creacionadmision' => Auth::user()->name, // Guardar el nombre del usuario autenticado

        ]);

        // Registrar el evento en la tabla 'eventos'
        Eventos::create([
            'accion' => 'Generar Admision',
            'descripcion' => 'Creación de admisión',
            'codigo' => $admision->codigo,
            'user_id' => Auth::id(),
        ]);
        Historico::create([
            'numero_guia' => $admision->codigo, // Asignar el código único de admisión al número de guía
            'fecha_hora_admision' => $admision->fecha, // Asignar la fecha de admisión
            'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
            'id_estado_actualizacion' => 1, // Estado inicial: 1
            'estado_actualizacion' => 'Admisión en el punto de recepción/recolección en el domicilio del remitente', // Descripción del estado
        ]);

        // Enlace QR fijo
        $qrLink = 'https://correos.gob.bo:8000/';
        // Preparar los datos para el PDF usando el registro recién creado
        $data = [
            'origen' => $admision->origen,
            'fecha' => $admision->fecha,
            'servicio' => $admision->servicio,
            'tipo_correspondencia' => $admision->tipo_correspondencia,
            'cantidad' => $admision->cantidad,
            'peso' => $admision->peso,
            'destino' => $admision->destino,
            'codigo' => $admision->codigo,
            'precio' => $admision->precio,
            'numero_factura' => $admision->numero_factura,
            'nombre_remitente' => $admision->nombre_remitente,
            'nombre_envia' => $admision->nombre_envia,
            'carnet' => $admision->carnet,
            'telefono_remitente' => $admision->telefono_remitente,
            'nombre_destinatario' => $admision->nombre_destinatario,
            'telefono_destinatario' => $admision->telefono_destinatario,
            'direccion' => $admision->direccion,
            'provincia' => $admision->provincia,
            'ciudad' => $admision->ciudad,
            'pais' => $admision->pais,
            'qrLink' => $qrLink, // Enlace QR fijo
            'contenido' => $admision->contenido, // Agrega este campo

        ];

        // Renderizar la vista y generar el PDF
        $pdf = Pdf::loadView('pdfs.admision', $data);

        // Descargar automáticamente el PDF
        return response()->streamDownload(
            fn() => print($pdf->stream('admision.pdf')),
            'admision.pdf'
        );
        return redirect()->to(request()->header('Referer'));

        // Mensaje de éxito
        $this->dispatch('reload-page');

    }

    public function enviarMensajeWhatsApp($admisionId)
    {
        $admision = Admision::findOrFail($admisionId);

        // Limpia el número de teléfono de caracteres no numéricos
        $telefono = preg_replace('/\D/', '', $admision->telefono_remitente);

        // Crea el mensaje personalizado con el nombre del remitente
        $mensaje = urlencode("Hola {$admision->nombre_remitente}, este es un mensaje relacionado con tu envío. Tu código de seguimiento es: {$admision->codigo}.");

        // Construir la URL de WhatsApp
        $url = "https://web.whatsapp.com/send?phone={$telefono}&text={$mensaje}";

        // Debug para verificar la URL generada
        // \Log::debug("WhatsApp URL: $url"); // Revisa este log en `storage/logs/laravel.log`

        // Enviar la URL al frontend
        $this->dispatch('abrir-whatsapp', ['url' => $url]);
    }






    public function edit($id)
    {
        $admision = Admision::findOrFail($id);
        $this->admisionId = $admision->id;

        // Llenar las propiedades con los datos de la admisión
        $this->origen = $admision->origen;
        $this->servicio = $admision->servicio;
        $this->tipo_correspondencia = $admision->tipo_correspondencia;
        $this->cantidad = $admision->cantidad;
        $this->peso = $admision->peso;
        $this->destino = $admision->destino;
        $this->codigo = $admision->codigo;
        $this->precio = $admision->precio;
        $this->numero_factura = $admision->numero_factura;
        $this->nombre_remitente = $admision->nombre_remitente;
        $this->nombre_envia = $admision->nombre_envia;
        $this->carnet = $admision->carnet;
        $this->telefono_remitente = $admision->telefono_remitente;
        $this->nombre_destinatario = $admision->nombre_destinatario;
        $this->telefono_destinatario = $admision->telefono_destinatario;
        $this->direccion = $admision->direccion;
        $this->provincia = $admision->provincia;
        $this->ciudad = $admision->ciudad;
        $this->pais = $admision->pais;

        // Abrir el modal de edición
        $this->dispatch('open-edit-modal');
    }



    public function update()
    {
        $this->validate();

        if ($this->admisionId) {
            $admision = Admision::findOrFail($this->admisionId);

            // Actualizar la admisión
            $admision->update([
                'origen' => $this->origen,
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
                'provincia' => $this->provincia,
                'ciudad' => $this->ciudad,
                'pais' => $this->pais,
                'contenido' => $this->contenido,
            ]);

            // Registrar el evento de edición
            Eventos::create([
                'accion' => 'Editar',
                'descripcion' => 'Edición de admisión',
                'codigo' => $admision->codigo,
                'user_id' => Auth::id(),
            ]);

            // Mensaje de éxito y cerrar modal
            session()->flash('message', 'Registro actualizado exitosamente.');
            $this->dispatch('close-edit-modal');
        }
    }







    public function delete($id)
    {
        $admision = Admision::findOrFail($id);

        // Cambiar el estado a 0 (inactivo)
        $admision->update(['estado' => 0]);

        // Registrar el evento de cambio de estado
        Eventos::create([
            'accion' => 'Eliminar',
            'descripcion' => 'Cambio de estado de admisión a Eliminada',
            'codigo' => $admision->codigo,
            'user_id' => Auth::id(),
        ]);

        session()->flash('message', 'La admisión ha sido eliminada exitosamente.');
    }



    public function getPriceByWeightAndDestination($peso, $destino)
    {
        $tarifas = [
        'POSTPAGO' => [
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
            'DEVOLUCION' => [
                [0.001, 0.250, 20],
                [0.251, 0.500, 24],
                [0.501, 1, 34],
                [1.001, 2, 46],
                [2.001, 3, 56],
                [3.001, 4, 68],
                [4.001, 5, 82],
                [5.001, 6, 96],
                [6.001, 7, 108],
                [7.001, 8, 120],
                [8.001, 9, 136],
                [9.001, 10, 148],
                [10.001, 11, 162],
                [11.001, 12, 174],
                [12.001, 13, 188],
                [13.001, 14, 202],
                [14.001, 15, 214],
                [15.001, 16, 228],
                [16.001, 17, 242],
                [17.001, 18, 254],
                [18.001, 19, 268],
                [19.001, 20, 280],
            ],

            'SUPEREXPRESS' => [
                [0.001, 0.250, 32],
                [0.251, 0.500, 39],
                [0.501, 1, 52],
                [1.001, 2, 68],
                [2.001, 3, 83],
                [3.001, 4, 99],
                [4.001, 5, 114],
                [5.001, 6, 130],
                [6.001, 7, 145],
                [7.001, 8, 161],
                [8.001, 9, 177],
                [9.001, 10, 192],
                [10.001, 11, 208],
                [11.001, 12, 223],
                [12.001, 13, 239],
                [13.001, 14, 255],
                [14.001, 15, 270],
                [15.001, 16, 286],
                [16.001, 17, 301],
                [17.001, 18, 317],
                [18.001, 19, 332],
                [19.001, 20, 348],

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

            // Si hay algún valor en telefono_destinatario, incrementa el precio en 2
            if (!empty($this->telefono_destinatario)) {
                $this->precio += 2;
            }
        } else {
            $this->precio = null;
        }
    }


    public function reimprimir($id)
    {
        $admision = Admision::findOrFail($id);

        $qrLink = 'https://correos.gob.bo:8000/';
        // Preparar los datos para el PDF usando el registro recién creado
        $data = [
            'origen' => $admision->origen,
            'fecha' => $admision->fecha,
            'servicio' => $admision->servicio,
            'tipo_correspondencia' => $admision->tipo_correspondencia,
            'cantidad' => $admision->cantidad,
            'peso' => $admision->peso,
            'destino' => $admision->destino,
            'codigo' => $admision->codigo,
            'precio' => $admision->precio,
            'numero_factura' => $admision->numero_factura,
            'nombre_remitente' => $admision->nombre_remitente,
            'nombre_envia' => $admision->nombre_envia,
            'carnet' => $admision->carnet,
            'telefono_remitente' => $admision->telefono_remitente,
            'nombre_destinatario' => $admision->nombre_destinatario,
            'telefono_destinatario' => $admision->telefono_destinatario,
            'direccion' => $admision->direccion,
            'provincia' => $admision->provincia,
            'ciudad' => $admision->ciudad,
            'pais' => $admision->pais,
            'qrLink' => $qrLink, // Enlace QR fijo
            'contenido' => $admision->contenido, // Agrega este campo

        ];

        // Registrar el evento con la hora actual
        Eventos::create([
            'accion' => 'Reimprimir',
            'descripcion' => 'Reimpresión de admisión',
            'codigo' => $admision->codigo,
            'user_id' => Auth::id(),
        ]);

        // Generar el PDF
        $pdf = Pdf::loadView('pdfs.admision', $data);

        // Retornar el PDF para descarga
        return response()->streamDownload(
            fn() => print($pdf->stream('admision.pdf')),
            'admision.pdf'
        );
    }



    public function entregarAExpedicion()
    {
        if (!empty($this->selectedAdmisiones)) {
            // Obtener las admisiones seleccionadas
            $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)
                ->where('origen', $this->origen)
                ->get();

            foreach ($admisiones as $admision) {
                // Cambiar el estado de la admisión a "Entregado a Expedición"
                $admision->update(['estado' => 2]);

                // Registrar el evento
                Eventos::create([
                    'accion' => 'Entregar a Expedición',
                    'descripcion' => 'Entrega a expedición de admisión seleccionada',
                    'codigo' => $admision->codigo,
                    'user_id' => Auth::id(),
                ]);
                Historico::create([
                    'numero_guia' => $admision->codigo, // Asignar el código único de admisión al número de guía
                    'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
                    'id_estado_actualizacion' => 2, // Estado inicial: 1
                    'estado_actualizacion' => ' En tránsito', // Descripción del estado
                ]);
            }
            // return $this->generarReporte($admisiones);

            // Mostrar mensaje de éxito
            session()->flash('message', 'Las admisiones seleccionadas fueron entregadas a expedición correctamente.');

            // Limpiar selección
            $this->selectedAdmisiones = [];
            $this->selectAll = false;
            return $this->generarReporte($admisiones);
        } else {
            session()->flash('error', 'No se seleccionaron admisiones para entregar a expedición.');
        }
    }




    public function updatedSelectAll($value)
    {
        // Si selectAll está activado, selecciona todos los IDs de la página actual
        $this->selectedAdmisiones = $value ? $this->currentPageIds : [];
    }
    public function abrirModalEntregarHoy()
    {
        $today = Carbon::today(); // Fecha actual sin hora

        // Obtener las admisiones generadas hoy
        $this->admisionesParaExpedicion = Admision::where('origen', $this->origen)
            ->whereDate('fecha', $today)
            ->where('estado', 1) // Solo admisiones activas
            ->get()
            ->toArray(); // Convertir a array para usar en la vista

        if (empty($this->admisionesParaExpedicion)) {
            session()->flash('error', 'No hay registros generados hoy para enviar a expedición.');
            return;
        }

        // Mostrar el modal
        $this->dispatch('mostrar-modal-expedicion-hoy');
    }

    // Método para confirmar y procesar la entrega
    public function confirmarEntregarHoy()
    {
        $today = Carbon::today();

        // Obtener las admisiones generadas hoy
        $admisiones = Admision::where('origen', $this->origen)
            ->whereDate('fecha', $today)
            ->where('estado', 1) // Solo procesar admisiones activas
            ->get();

        if ($admisiones->isEmpty()) {
            session()->flash('error', 'No hay registros generados hoy para entregar a expedición.');
            return;
        }

        foreach ($admisiones as $admision) {
            // Actualizar estado de la admisión
            $admision->update(['estado' => 2]);

            // Registrar evento
            Eventos::create([
                'accion' => 'Entregar a Expedición',
                'descripcion' => 'Entrega a expedición de admisión generada hoy',
                'codigo' => $admision->codigo,
                'user_id' => Auth::id(),
            ]);
            Historico::create([
                'numero_guia' => $admision->codigo, // Asignar el código único de admisión al número de guía
                'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
                'id_estado_actualizacion' => 2, // Estado inicial: 1
                'estado_actualizacion' => ' En tránsito', // Descripción del estado
            ]);
        }
        $this->dispatch('reload-page');

        // Generar y descargar el PDF
        return $this->generarReporte($admisiones);
    }



    public function generarReporte($admisiones)
    {
        if ($admisiones->isEmpty()) {
            session()->flash('error', 'No hay registros para generar el reporte.');
            return;
        }

        // Preparar los datos para la vista
        $data = [
            'admisiones' => $admisiones,
        ];

        // Generar el PDF
        $pdf = Pdf::loadView('pdfs.reporte_admisiones', $data);

        // Descargar automáticamente el PDF
        return response()->streamDownload(
            fn() => print($pdf->stream('reporte_admisiones.pdf')),
            'reporte_admisiones.pdf'
        );
    }



    public function updatedServicio()
    {
        // Mapeo de servicios a prefijos
        $prefixes = [
            'EMS' => 'EN',
            'SUPEREXPRESS' => 'EX',
            // Agrega otros servicios y prefijos según sea necesario
        ];
        $prefix = isset($prefixes[$this->servicio]) ? $prefixes[$this->servicio] : 'XX';

        $cityCodes = [
            'LA PAZ' => '0',
            'COCHABAMBA' => '1',
            'SANTA CRUZ' => '2',
            'ORURO' => '3',
            'POTOSI' => '4',
            'CHUQUISACA' => '5',
            'TARIJA' => '6',
            'PANDO' => '7',
            'BENI' => '8',
            // Agrega más ciudades y sus códigos según sea necesario
        ];
        $city = Auth::user()->city;
        $cityCode = isset($cityCodes[$city]) ? $cityCodes[$city] : '0';

        // Obtener los últimos dos dígitos del año actual
        $yearSuffix = now()->format('y');

        // Obtener el número máximo utilizado para este servicio, ciudad y año
        // Obtener el número máximo utilizado para este servicio, ciudad y año
        $lastNumber = Admision::where('codigo', 'like', $prefix . $cityCode . $yearSuffix . '%')
            ->selectRaw("MAX(CAST(REGEXP_REPLACE(SUBSTRING(codigo FROM 6), '[^0-9]', '', 'g') AS INTEGER)) as max_number")
            ->value('max_number');

        $newNumber = $lastNumber ? $lastNumber + 1 : 1;
        $numberPart = str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        // Sufijo es 'BO'
        $suffix = 'BO';

        // Generar el nuevo código
        $this->codigo = $prefix . $cityCode . $yearSuffix . $numberPart . $suffix;
    }
    public function deleteAdmision()
{
    if ($this->admisionId) {
        $admision = Admision::findOrFail($this->admisionId);

        // Cambiar el estado de la admisión a inactivo (soft delete)
        $admision->update(['estado' => 0]);

        // Registrar el evento de eliminación
        Eventos::create([
            'accion' => 'Baja',
            'descripcion' => 'Se dio de baja a la admisión',
            'codigo' => $admision->codigo,
            'user_id' => Auth::id(),
        ]);

        // Mensaje de éxito y cerrar el modal
        session()->flash('message', 'Admisión eliminada correctamente.');
        $this->dispatch('close-edit-modal'); // Cierra el modal
        $this->resetInputFields(); // Limpia los campos del formulario
    } else {
        session()->flash('error', 'No se pudo eliminar la admisión.');
    }
}

}
