<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Models\Eventos; // Asegúrate de importar el modelo Evento
use Livewire\WithFileDownloads; // Importar el trait



class Emsinventario extends Component
{
    use WithPagination;
    public $currentPageIds = [];
    public $searchTerm = '';
    public $perPage = 10000000;
    public $admisionId;
    public $selectedAdmisiones = [];
    public $showModal = false;
    public $destinoModal;
    public $ciudadModal;
    public $selectedAdmisionesCodes = [];
    public $selectAll = false;
    public $showReencaminamientoModal = false;
    public $selectedDepartment = null; // Almacena el departamento seleccionado en el modal
    public $lastSearchTerm = ''; // Almacena el término de la última búsqueda
    public $selectedCity = null;
    public $cityJustUpdated = false;
    public $showEditModal = false; // Controla la visibilidad del modal de edición
    public $editAdmisionId = null; // Almacena el ID de la admisión que se está editando
    public $editDireccion = ''; // Dirección editable
    public $manualManifiesto = '';   // Para el valor del Manifiesto ingresado manualmente
    public $useManualManifiesto = false; // Checkbox que indica si se usará el Manifiesto manual
    public $currentManifiesto = null; // Almacena el manifiesto actual
    public $selectedTransport = 'AEREO';  // Por defecto AÉREO
    public $numeroVuelo = '';            // Campo para el número de vuelo
    
    public $origen, $fecha, $servicio, $tipo_correspondencia, $cantidad, $peso, $destino, $codigo, $precio, $numero_factura, $nombre_remitente, $nombre_envia, $carnet, $telefono_remitente, $nombre_destinatario, $telefono_destinatario, $direccion, $ciudad, $pais, $provincia, $contenido;

    public $showReprintModal = false; // Controla la visibilidad del modal de reimpresión
    public $inputManifiesto = ''; // Almacena el manifiesto ingresado para la búsqueda

    public $showReimprimirModal = false;
    public $manifiestoInput = '';

    public function updatedSelectedCity()
    {
        $this->resetPage(); // Reseteamos la paginación a la página 1
        $this->cityJustUpdated = true; // Indicamos que la ciudad ha sido actualizada
    }

    public function render()
    {
        $userCity = Auth::user()->city;

        // Filtrar las admisiones según las condiciones
        $admisiones = Admision::query()
            ->where(function ($query) use ($userCity) {
                // Condición para estado 7
                $query->where(function ($subQuery) use ($userCity) {
                    $subQuery->where('estado', 7)
                        ->where(function ($innerQuery) use ($userCity) {
                            $innerQuery->where('reencaminamiento', $userCity) // Si hay reencaminamiento, usarlo
                                ->orWhere(function ($orQuery) use ($userCity) {
                                    $orQuery->whereNull('reencaminamiento') // Si no hay reencaminamiento
                                        ->where('ciudad', $userCity);    // Usar ciudad
                                });
                        });
                })
                    ->orWhere(function ($subQuery) use ($userCity) {
                        // Condición para estado 3
                        $subQuery->where('estado', 3)
                            ->where('origen', $userCity); // Usar origen
                    })
                    ->orWhere(function ($subQuery) use ($userCity) {
                        // Condición para estado 10
                        $subQuery->where('estado', 10)
                            ->where(function ($innerQuery) use ($userCity) {
                                $innerQuery->where('reencaminamiento', $userCity) // Si hay reencaminamiento, usarlo
                                    ->orWhere(function ($orQuery) use ($userCity) {
                                        $orQuery->whereNull('reencaminamiento') // Si no hay reencaminamiento
                                            ->where('ciudad', $userCity);    // Usar ciudad
                                    });
                            });
                    });
            })
            ->where('codigo', 'like', '%' . $this->searchTerm . '%'); // Filtro por código

        // Filtrar por ciudad seleccionada, si aplica
        if ($this->selectedCity) {
            $admisiones = $admisiones->where(function ($query) {
                $query->where('ciudad', $this->selectedCity)
                    ->orWhere('reencaminamiento', $this->selectedCity);
            });
        }

        $admisiones = $admisiones->orderBy('fecha', 'desc') // Ordenar por fecha descendente
            ->paginate($this->perPage);

        // Marcar automáticamente los checkboxes de las admisiones mostradas
        if ($this->cityJustUpdated) {
            $currentPageIds = $admisiones->pluck('id')->toArray();
            $this->selectedAdmisiones = $currentPageIds;
            $this->cityJustUpdated = false;
        }

        return view('livewire.emsinventario', [
            'admisiones' => $admisiones,
        ]);
    }








    public function toggleSelectAll()
    {
        $this->selectAll = !$this->selectAll;

        if ($this->selectAll) {
            // Obtener todos los IDs visibles en la página actual
            $visibleAdmisiones = Admision::query()
                ->where(function ($query) {
                    $query->where('estado', 3)
                        ->orWhere('estado', 7);
                })
                ->where('codigo', 'like', '%' . $this->searchTerm . '%')
                ->orderBy('fecha', 'desc')
                ->limit($this->perPage)
                ->pluck('id')
                ->toArray();

            // Actualizar la selección
            $this->selectedAdmisiones = array_unique(array_merge($this->selectedAdmisiones, $visibleAdmisiones));
        } else {
            // Deseleccionar todos los elementos de la página actual
            $visibleAdmisiones = Admision::query()
                ->where(function ($query) {
                    $query->where('estado', 3)
                        ->orWhere('estado', 7);
                })
                ->where('codigo', 'like', '%' . $this->searchTerm . '%')
                ->orderBy('fecha', 'desc')
                ->limit($this->perPage)
                ->pluck('id')
                ->toArray();

            $this->selectedAdmisiones = array_diff($this->selectedAdmisiones, $visibleAdmisiones);
        }
    }




    public function abrirModal()
    {
        if (count($this->selectedAdmisiones) > 0) {
            $admissions = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            if ($admissions->isEmpty()) {
                session()->flash('error', 'No hay admisiones seleccionadas.');
                return;
            }

            $this->showModal = true;

            $this->destinoModal = null;
            $this->ciudadModal = null;

            $this->selectedAdmisionesCodes = $admissions->pluck('codigo')->toArray();

            // Inicializar el campo de reencaminamiento en null
            $this->selectedDepartment = null;
        } else {
            session()->flash('error', 'Debe seleccionar al menos una admisión.');
        }
    }















    public function abrirModalReimprimir()
    {
        $this->showReimprimirModal = true;
    }
    public function generarExcel($admisiones = null)
    {
        // Si no se pasa $admisiones como argumento, buscar por currentManifiesto o selectedAdmisiones
        if ($admisiones === null) {
            if (!empty($this->currentManifiesto)) {
                $admisiones = Admision::where('manifiesto', $this->currentManifiesto)->get();
            } else {
                $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();
            }
        }

        if ($admisiones->isEmpty()) {
            session()->flash('error', 'No hay admisiones válidas para generar el Excel.');
            return;
        }

        // Crear el documento
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('Designado Operador Postal');

        // Fecha y hora actual
        $currentDate = now()->format('d/m/Y');
        $currentTime = now()->format('H:i');
        $firstPackage = $admisiones->first();

        // Nombre del usuario logueado
        $loggedInUserCity = Auth::user()->city;

        // Estilo para encabezado
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['vertical' => 'center', 'horizontal' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ];

        // Configurar el ancho de las columnas
        $worksheet->getColumnDimension('A')->setWidth(20);
        $worksheet->getColumnDimension('B')->setWidth(10);
        $worksheet->getColumnDimension('C')->setWidth(10);
        $worksheet->getColumnDimension('D')->setWidth(15);
        $worksheet->getColumnDimension('E')->setWidth(25);
        $worksheet->getColumnDimension('F')->setWidth(20);
        $worksheet->getColumnDimension('G')->setWidth(30);
        $worksheet->getColumnDimension('H')->setWidth(30);

        // Fila 1: Título
        $worksheet->mergeCells('A1:C2');
        $worksheet->setCellValue('A1', 'Postal designated operator');
        $worksheet->getStyle('A1')->applyFromArray($headerStyle);

        // Añadir la imagen EMS en lugar del texto "EMS"
        $drawing = new Drawing();
        $drawing->setName('EMS Image');
        $drawing->setDescription('EMS Logo');
        $drawing->setPath(public_path('images/EMS1.png')); // Ruta de la imagen
        $drawing->setCoordinates('D1'); // Celda de inicio
        $drawing->setHeight(80); // Altura de la imagen
        $drawing->setWorksheet($worksheet);

        $worksheet->mergeCells('H1:M2');
        $worksheet->setCellValue('H1', 'LISTA CN-33');
        $worksheet->getStyle('H1')->applyFromArray($headerStyle);

        // Fila 3: BO-BOLIVIA y Airmails
        $worksheet->mergeCells('A3:C3');
        $worksheet->setCellValue('A3', 'BO-BOLIVIA');
        $worksheet->getStyle('A3')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H3:M3');
        $worksheet->setCellValue('H3', 'Airmails');
        $worksheet->getStyle('H3')->applyFromArray($headerStyle);

        // Fila 4: Office of origin y DIA
        $worksheet->mergeCells('A4:C4');
        $worksheet->setCellValue('A4', 'Office of origin');
        $worksheet->getStyle('A4')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H4:M4');
        $worksheet->setCellValue('H4', 'DIA');
        $worksheet->getStyle('H4')->applyFromArray($headerStyle);

        // Fila 5: Origen y Fecha actual
        $worksheet->mergeCells('A5:C5');
        $worksheet->setCellValue('A5', $loggedInUserCity);
        $worksheet->getStyle('A5')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H5:M5');
        $worksheet->setCellValue('H5', $currentDate);
        $worksheet->getStyle('H5')->applyFromArray($headerStyle);

        // Fila 6: Office of destination y HORA
        $worksheet->mergeCells('A6:C6');
        $worksheet->setCellValue('A6', 'Office of destination');
        $worksheet->getStyle('A6')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H6:M6');
        $worksheet->setCellValue('H6', 'HORA');
        $worksheet->getStyle('H6')->applyFromArray($headerStyle);

        // Fila 7: Destino y Hora actual
        $destinationCity = $this->selectedDepartment ?? $firstPackage->reencaminamiento ?? $firstPackage->ciudad ?? '';
        $worksheet->mergeCells('A7:C7');
        $worksheet->setCellValue('A7', $destinationCity);
        $worksheet->getStyle('A7')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H7:M7');
        $worksheet->setCellValue('H7', $currentTime);
        $worksheet->getStyle('H7')->applyFromArray($headerStyle);

        // Fila 8: DESPACHO - Manifiesto
        $worksheet->mergeCells('A8:M8');
        $worksheet->setCellValue('A8', 'DESPACHO - ' . $this->currentManifiesto);
        $worksheet->getStyle('A8')->applyFromArray($headerStyle);

        // Fila 9: NUMERO DE VUELO - 1
        $worksheet->mergeCells('A9:M9');
        $worksheet->setCellValue('A9', 'NUMERO DE VUELO - 1');
        $worksheet->getStyle('A9')->applyFromArray($headerStyle);

        // Fila 11: Encabezado de columnas
        $worksheet->setCellValue('A11', 'ENVIO');
        $worksheet->setCellValue('B11', 'CAN');
        $worksheet->setCellValue('C11', 'COR');
        $worksheet->setCellValue('D11', 'EMS');
        $worksheet->setCellValue('E11', 'CLIENTE');
        $worksheet->setCellValue('F11', 'ENDAS');
        $worksheet->setCellValue('G11', 'OFICIAL');
        $worksheet->setCellValue('H11', 'OBSERVACION');
        $worksheet->getStyle('A11:H11')->applyFromArray($headerStyle);

        // Agregar los datos de admisiones seleccionadas
        $currentRow = 12;
        $totalCantidad = 0;
        $totalPeso = 0;

        foreach ($admisiones as $admision) {
            $peso = $admision->peso_ems ?: $admision->peso; // Usa peso_ems o peso si está vacío

            $worksheet->setCellValue("A$currentRow", $admision->codigo);
            $worksheet->setCellValue("B$currentRow", 1);
            $worksheet->setCellValue("C$currentRow", '');
            $worksheet->setCellValue("D$currentRow", $peso);
            $worksheet->setCellValue("E$currentRow", $admision->nombre_remitente);
            $worksheet->setCellValue("F$currentRow", '');
            $worksheet->setCellValue("G$currentRow", '');
            $worksheet->setCellValue("H$currentRow", $admision->observacion);
            $worksheet->getStyle("A$currentRow:H$currentRow")->applyFromArray($headerStyle);

            $totalCantidad += 1;
            $totalPeso += $peso;

            $currentRow++;
        }

        // Fila de totales
        $worksheet->setCellValue("A$currentRow", 'TOTAL');
        $worksheet->setCellValue("B$currentRow", $totalCantidad);
        $worksheet->setCellValue("D$currentRow", $totalPeso);
        $worksheet->getStyle("A$currentRow:H$currentRow")->applyFromArray($headerStyle);
        $currentRow += 2;

        // Información adicional
        $worksheet->setCellValue("A$currentRow", 'Dispatching office of exchange');
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);
        $currentRow++;

        $worksheet->setCellValue("A$currentRow", $loggedInUserCity);
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);
        $currentRow++;

        $worksheet->setCellValue("A$currentRow", 'Signature');
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);
        $currentRow++;

        $worksheet->setCellValue("A$currentRow", '______________________');
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);
        $currentRow++;

        $worksheet->setCellValue("A$currentRow", 'Salidas Internacionales');
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);

        $worksheet->setCellValue("F" . ($currentRow - 2), 'Office of exchange of destination');
        $worksheet->getStyle("F" . ($currentRow - 2))->applyFromArray($headerStyle);
        $worksheet->setCellValue("F$currentRow", 'Date and signature');


        // Guardar el archivo temporalmente
        $fileName = 'designado_operador_postal.xlsx';
        $filePath = storage_path("app/public/$fileName");

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Retornar la descarga del archivo
        return response()->download($filePath)->deleteFileAfterSend(true);
    }






    public function mandarAVentanilla()
    {
        if (count($this->selectedAdmisiones) > 0) {
            $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            foreach ($admisiones as $admision) {
                // Cambiar el estado a 9
                $admision->estado = 11;
                $admision->save();

                // Registrar el evento
                Eventos::create([
                    'accion' => 'Mandar a ventanilla',
                    'descripcion' => 'La admisión fue enviada a la ventanilla.',
                    'codigo' => $admision->codigo,
                    'user_id' => Auth::id(),
                ]);
            }

            // Limpiar la selección después de procesar
            $this->selectedAdmisiones = [];

            session()->flash('message', 'Las admisiones seleccionadas se han enviado a la ventanilla.');
        } else {
            session()->flash('error', 'Debe seleccionar al menos una admisión.');
        }
    }
    public function abrirEditModal($admisionId)
    {
        $admision = Admision::find($admisionId);
        if ($admision) {
            $this->editAdmisionId = $admision->id;
            $this->editDireccion = $admision->direccion;
            $this->showEditModal = true;
        } else {
            session()->flash('error', 'No se encontró la admisión.');
        }
    }

    // Método para guardar los cambios
    public function guardarEdicion()
    {
        $admision = Admision::find($this->editAdmisionId);
        if ($admision) {
            $admision->direccion = $this->editDireccion;
            $admision->save();

            session()->flash('message', 'La dirección ha sido actualizada correctamente.');
            $this->showEditModal = false;
            Eventos::create([
                'accion' => 'Cambio de Direccion',
                'descripcion' => 'El encargado cambio la direccion.',
                'codigo' => $admision->codigo,
                'user_id' => Auth::id(),
            ]);
            // Recargar la página
            return redirect(request()->header('Referer'));
        } else {
            session()->flash('error', 'No se encontró la admisión para actualizar.');
        }
    }

    public function mandarARegional()
    {
        if (empty($this->selectedAdmisiones)) {
            session()->flash('error', 'Debe seleccionar al menos una admisión.');
            return;
        }
    
        $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();
    
        if ($admisiones->isEmpty()) {
            session()->flash('error', 'No se encontraron admisiones válidas seleccionadas.');
            return;
        }
    
        // Si el usuario introdujo un Manifiesto manual
        if (!empty($this->manualManifiesto)) {
            $this->currentManifiesto = $this->manualManifiesto;
        } else {
            // Generamos uno automáticamente
            $this->currentManifiesto = $this->generarManifiesto(Auth::user()->city);
        }
    
        // Asignar el manifiesto a las admisiones y cambiar estado
        foreach ($admisiones as $admision) {
            $admision->estado = 6; // Mandado a regional
            $admision->manifiesto = $this->currentManifiesto;
            $admision->save();
    
            Eventos::create([
                'accion' => 'Mandar a regional',
                'descripcion' => "La admisión fue enviada a la regional con el manifiesto {$this->currentManifiesto}.",
                'codigo' => $admision->codigo,
                'user_id' => Auth::id(),
            ]);
        }
    
        // Aquí pasamos $this->selectedTransport y $this->numeroVuelo
        return $this->generarPdf($admisiones, $this->selectedTransport, $this->numeroVuelo);
    }
    





    private function generarManifiesto($city)
    {
        return \DB::transaction(function () use ($city) {
            // Obtener el prefijo según la ciudad
            $prefix = $this->cityPrefixes[$city] ?? '9'; // Default a '9' si la ciudad no está en el mapeo

            // Obtener el último manifiesto registrado para la ciudad
            $ultimoManifiesto = Admision::where('manifiesto', 'like', "BO$prefix%")
                ->orderBy('manifiesto', 'desc')
                ->value('manifiesto');

            // Generar el nuevo manifiesto
            $nuevoNumero = $ultimoManifiesto ? (int) substr($ultimoManifiesto, 3) + 1 : 1;

            // Formatear el nuevo manifiesto
            return 'BO' . $prefix . str_pad($nuevoNumero, 7, '0', STR_PAD_LEFT);
        });
    }
    public function mount()
    {
        $this->origen = Auth::user()->city; // Si tienes una ciudad por defecto
        $this->ciudad = ""; // Cambia este valor si quieres que otra ciudad sea la predeterminada
        $this->cantidad = 1; // Establece cantidad en 1
    }
    public function store()
    {
        // Establecer precio predeterminado en 0 si no está definido
        $this->precio = $this->precio ?? 0;

        // Establecer la fecha actual
        $this->fecha = now();

        // Solo generar un código si no se ha ingresado manualmente
        if (empty($this->codigo)) {
            // Generar el código dinámicamente
            $prefixes = [
                'EMS' => 'EN',
                'OFICIAL' => 'RD',
                'CRIAS' => 'DE',
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

            $yearSuffix = now()->format('y');
            $lastNumber = Admision::where('codigo', 'like', $prefix . $cityCode . $yearSuffix . '%')
                ->selectRaw("MAX(CAST(REGEXP_REPLACE(SUBSTRING(codigo FROM 6), '[^0-9]', '', 'g') AS INTEGER)) as max_number")
                ->value('max_number');

            $newNumber = $lastNumber ? $lastNumber + 1 : 1;
            $numberPart = str_pad($newNumber, 6, '0', STR_PAD_LEFT);

            $suffix = 'BO';
            $this->codigo = $prefix . $cityCode . $yearSuffix . $numberPart . $suffix;
        }

        // Crear el registro
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
            'estado' => 3,
            'user_id' => Auth::id(),
            'creacionadmision' => Auth::user()->name,
        ]);

        Eventos::create([
            'accion' => 'Crear',
            'descripcion' => 'Creación de admisión Oficial',
            'codigo' => $admision->codigo,
            'user_id' => Auth::id(),
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
        session()->flash('message', 'Admisión creada exitosamente.');
    }


    public function reimprimirManifiesto()
    {
        $this->validate([
            'manifiestoInput' => 'required|string'
        ]);

        $admisiones = Admision::where('manifiesto', $this->manifiestoInput)->get();

        if ($admisiones->isEmpty()) {
            session()->flash('error', 'No se encontraron admisiones con el manifiesto ingresado.');
            return;
        }

        $this->currentManifiesto = $this->manifiestoInput;

        // Llama al método para generar el PDF con las admisiones
        return $this->generarPdf($admisiones);
    }


    public function generarPdf($admisiones = null, $selectedTransport = null, $numeroVuelo = null)
    {
        // Si no se pasan $admisiones, buscamos por currentManifiesto o selectedAdmisiones
        if ($admisiones === null) {
            if (!empty($this->currentManifiesto)) {
                $admisiones = Admision::where('manifiesto', $this->currentManifiesto)->get();
            } else {
                $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();
            }
        }
    
        if ($admisiones->isEmpty()) {
            session()->flash('error', 'No hay admisiones válidas para generar el PDF.');
            return;
        }
    
        // Prepara datos para la vista
        $currentDate = now()->format('d/m/Y');
        $currentTime = now()->format('H:i');
        $firstPackage = $admisiones->first();
    
        $loggedInUserCity = Auth::user()->city;
        $destinationCity = $this->selectedDepartment
            ?? $firstPackage->reencaminamiento
            ?? $firstPackage->ciudad
            ?? '';
    
        $data = [
            'admisiones'        => $admisiones,
            'currentDate'       => $currentDate,
            'currentTime'       => $currentTime,
            'currentManifiesto' => $this->currentManifiesto,
            'loggedInUserCity'  => $loggedInUserCity,
            'destinationCity'   => $destinationCity,
            'selectedTransport' => $selectedTransport, // Aéreo o Terrestre
            'numeroVuelo'       => $numeroVuelo,       // Número de vuelo manual
        ];
    
        // Generamos el PDF con DomPDF
        $pdf = Pdf::loadView('pdfs.cn33', $data)->setPaper('letter', 'portrait');
    
        // Descarga el PDF directamente
        return response()->streamDownload(
            fn() => print($pdf->stream('cn-33.pdf')),
            'cn-33.pdf'
        );
    }
    


    private $cityPrefixes = [
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
}
