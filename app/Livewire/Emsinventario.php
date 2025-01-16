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
















    public function generarExcel()
    {
        if (!empty($this->currentManifiesto)) {
            // Generar el Excel para TODAS las admisiones que tengan este manifiesto
            $admisiones = Admision::where('manifiesto', $this->currentManifiesto)->get();
        } else {
            // Comportamiento anterior
            $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();
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
        $worksheet->getColumnDimension('A')->setWidth(20); // ENVIO
        $worksheet->getColumnDimension('B')->setWidth(10); // CAN
        $worksheet->getColumnDimension('C')->setWidth(10); // COR
        $worksheet->getColumnDimension('D')->setWidth(15); // EMS
        $worksheet->getColumnDimension('E')->setWidth(25); // CLIENTE
        $worksheet->getColumnDimension('F')->setWidth(20); // ENDAS
        $worksheet->getColumnDimension('G')->setWidth(30); // OFICIAL
        $worksheet->getColumnDimension('H')->setWidth(30); // OBSERVACION

        // Fila 1: Título
        $worksheet->mergeCells('A1:C2');
        $worksheet->setCellValue('A1', 'Postal designated operator');
        $worksheet->getStyle('A1')->applyFromArray($headerStyle);

        $worksheet->mergeCells('D1:G7');
        $worksheet->setCellValue('D1', 'EMS');
        $worksheet->getStyle('D1')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H1:M2');
        $worksheet->setCellValue('H1', 'LISTA CN-33');
        $worksheet->getStyle('H1')->applyFromArray($headerStyle);

        // Añadir la imagen ocupando el rango H5:M7
        $drawing = new Drawing();
        $drawing->setName('EMS Image');
        $drawing->setDescription('EMS Logo');
        $drawing->setPath(public_path('images/EMS.png')); // Ruta de la imagen
        $drawing->setCoordinates('H5'); // Celda de inicio
        $drawing->setHeight(50);
        $drawing->setWorksheet($worksheet);

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
    
        // Obtener las admisiones seleccionadas
        $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();
    
        if ($admisiones->isEmpty()) {
            session()->flash('error', 'No se encontraron admisiones válidas seleccionadas.');
            return;
        }
    
        // Verificar si el usuario introdujo un Manifiesto
        if (!empty($this->manualManifiesto)) {
            // Usar manifiesto manual
            $this->currentManifiesto = $this->manualManifiesto;
        } else {
            // Generar un manifiesto automático
            $this->currentManifiesto = $this->generarManifiesto(Auth::user()->city);
        }
    
        // Asignar el manifiesto a las admisiones seleccionadas
        foreach ($admisiones as $admision) {
            $admision->estado = 6; // Cambiar estado a "Mandado a regional"
            $admision->manifiesto = $this->currentManifiesto;
            $admision->save();
    
            // Registrar el evento
            Eventos::create([
                'accion' => 'Mandar a regional',
                'descripcion' => "La admisión fue enviada a la regional con el manifiesto {$this->currentManifiesto}.",
                'codigo' => $admision->codigo,
                'user_id' => Auth::id(),
            ]);
        }
    
        // Generar el Excel usando el manifiesto actual
        return $this->generarExcel();
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
