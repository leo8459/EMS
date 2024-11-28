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
    public $perPage = 10;
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
        $newSelections = Admision::where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('estado', 3)
                             ->where('origen', Auth::user()->city)
                             ->where('ciudad', '!=', Auth::user()->city);
                })
                ->orWhere(function ($subQuery) {
                    $subQuery->where('estado', 7)
                             ->where('ciudad', Auth::user()->city)
                             ->where('origen', '!=', Auth::user()->city);
                });
            })
            ->where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->orderBy('fecha', 'desc')
            ->limit($this->perPage)
            ->pluck('id')
            ->toArray();

        // Agregar nuevas selecciones sin sobrescribir las existentes
        $this->selectedAdmisiones = array_unique(array_merge($this->selectedAdmisiones, $newSelections));
    } else {
        // Deseleccionar todos
        $this->selectedAdmisiones = [];
    }
}

    

public function abrirModal()
{
    if (count($this->selectedAdmisiones) > 0) {
        $admissions = Admision::whereIn('id', $this->selectedAdmisiones)
            ->where('origen', Auth::user()->city) // Validar ciudad del usuario
            ->get();

        if ($admissions->isEmpty()) {
            session()->flash('error', 'No hay admisiones válidas seleccionadas para su regional.');
            return;
        }

        $this->showModal = true;

        $this->destinoModal = null; // Ya no se utiliza
        $this->ciudadModal = null; // Ya no se utiliza

        $this->selectedAdmisionesCodes = $admissions->pluck('codigo')->toArray();

        // Inicializar el campo de reencaminamiento en null
        $this->selectedDepartment = null;
    } else {
        session()->flash('error', 'Debe seleccionar al menos una admisión.');
    }
}


    


public function mandarARegional()
{
    if (empty($this->selectedAdmisiones)) {
        session()->flash('error', 'No hay admisiones seleccionadas.');
        return;
    }

    if (empty($this->selectedDepartment)) {
        session()->flash('error', 'Debe seleccionar un departamento antes de enviar a la regional.');
        return;
    }

    $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)
        ->where('origen', Auth::user()->city)
        ->get();

    if ($admisiones->isEmpty()) {
        session()->flash('error', 'No hay admisiones válidas seleccionadas para procesar.');
        return;
    }

    foreach ($admisiones as $admision) {
        $admision->estado = 6; // Mandar a regional
        $admision->reencaminamiento = $this->selectedDepartment; // Asignar el departamento seleccionado
        $admision->save();

        Eventos::create([
            'accion' => 'Mandar a regional',
            'descripcion' => 'La admisión fue enviada a la regional.',
            'codigo' => $admision->codigo,
            'user_id' => Auth::id(),
        ]);
    }

    // Generar el Excel y retornar la descarga
    return $this->generarExcel();
}






    

    public function generarExcel()
    {
        $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)
        ->where('origen', Auth::user()->city)
        ->get();

        if ($admisiones->isEmpty()) {
            session()->flash('error', 'No hay admisiones válidas seleccionadas para generar el Excel.');
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
        $loggedInUserName = Auth::user()->name;
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
        // Usar el departamento seleccionado o el reencaminamiento si está disponible, o la ciudad de la admisión
        $destinationCity = $this->selectedDepartment ?? $firstPackage->reencaminamiento ?? $firstPackage->ciudad ?? '';

        $worksheet->mergeCells('A7:C7');
        $worksheet->setCellValue('A7', $destinationCity);
        $worksheet->getStyle('A7')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H7:M7');
        $worksheet->setCellValue('H7', $currentTime);
        $worksheet->getStyle('H7')->applyFromArray($headerStyle);

        // Fila 8: Despacho y Prioritario
        $worksheet->mergeCells('A8:G8');
        $worksheet->setCellValue('A8', 'DESPACHO -001');
        $worksheet->getStyle('A8')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H8:M8');
        $worksheet->setCellValue('H8', 'X PRIORITARIO                  X POR AEREO');
        $worksheet->getStyle('H8')->applyFromArray($headerStyle);

        // Fila 9: Número de vuelo y día de despacho
        $worksheet->mergeCells('A9:G10');
        $worksheet->setCellValue('A9', 'NUMERO DE VUELO LPB-OB-680');
        $worksheet->getStyle('A9')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H9:M9');
        $worksheet->setCellValue('H9', "DIA DE DESPACHO $currentDate");
        $worksheet->getStyle('H9')->applyFromArray($headerStyle);

        $worksheet->mergeCells('H10:M10');
        $worksheet->setCellValue('H10', "HORA $currentTime");
        $worksheet->getStyle('H10')->applyFromArray($headerStyle);

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
            $worksheet->setCellValue("B$currentRow", 1); // Cantidad fija (1)
            $worksheet->setCellValue("C$currentRow", ''); // Campo vacío para COR
            $worksheet->setCellValue("D$currentRow", $peso); // Mostrar el peso
            $worksheet->setCellValue("E$currentRow", $admision->nombre_remitente);
            $worksheet->setCellValue("F$currentRow", ''); // Campo vacío para ENDAS
            $worksheet->setCellValue("G$currentRow", ''); // Campo vacío para OFICIAL
            $worksheet->setCellValue("H$currentRow", $admision->observacion);
            $worksheet->getStyle("A$currentRow:H$currentRow")->applyFromArray($headerStyle);

            // Acumular cantidad y peso
            $totalCantidad += 1;
            $totalPeso += $peso;

            $currentRow++;
        }

        // Fila de totales
        $worksheet->setCellValue("A$currentRow", 'TOTAL');
        $worksheet->setCellValue("B$currentRow", $totalCantidad);
        $worksheet->setCellValue("C$currentRow", ''); // Campo vacío para COR
        $worksheet->setCellValue("D$currentRow", $totalPeso);
        $worksheet->setCellValue("E$currentRow", ''); // Campo vacío para CLIENTE
        $worksheet->setCellValue("F$currentRow", ''); // Campo vacío para ENDAS
        $worksheet->setCellValue("G$currentRow", ''); // Campo vacío para OFICIAL
        $worksheet->setCellValue("H$currentRow", ''); // Campo vacío para OBSERVACION
        $worksheet->getStyle("A$currentRow:H$currentRow")->applyFromArray($headerStyle);

        // Información adicional al final del documento
        $currentRow += 2; // Dejar espacio después de la tabla

        // Primera sección: Dispatching office of exchange
        $worksheet->setCellValue("A$currentRow", 'Dispatching office of exchange');
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);
        $currentRow++;

        $worksheet->setCellValue("A$currentRow", $loggedInUserCity); // Ciudad del usuario logueado
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);
        $currentRow++;

        $worksheet->setCellValue("A$currentRow", 'Signature');
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);
        $currentRow++;

        $worksheet->setCellValue("A$currentRow", '______________________'); // Espacio para la firma
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);
        $currentRow++;

        // Segunda sección: Salidas internacionales
        $worksheet->setCellValue("A$currentRow", 'Salidas Internacionales');
        $worksheet->getStyle("A$currentRow")->applyFromArray($headerStyle);
        $currentRow += 2; // Dejar espacio

        // Sección derecha: The official of the carrier of airport
        $carrierRow = $currentRow - 6;
        $worksheet->setCellValue("D$carrierRow", 'The official of the carrier of airport');
        $worksheet->getStyle("D$carrierRow")->applyFromArray($headerStyle);
        $carrierRow++;

        $worksheet->setCellValue("D$carrierRow", 'Date and signature');
        $worksheet->getStyle("D$carrierRow")->applyFromArray($headerStyle);

        // Sección derecha: Office of exchange of destination
        $destinationRow = $currentRow - 6;
        $worksheet->setCellValue("F$destinationRow", 'Office of exchange of destination');
        $worksheet->getStyle("F$destinationRow")->applyFromArray($headerStyle);
        $destinationRow++;

        $worksheet->setCellValue("F$destinationRow", 'Date and signature');
        $worksheet->getStyle("F$destinationRow")->applyFromArray($headerStyle);

        // Aplicar estilos adicionales si es necesario
        $worksheet->getStyle("A1:H$currentRow")->getAlignment()->setWrapText(true);

        // Guardar el archivo en el servidor temporalmente
        $fileName = 'designado_operador_postal.xlsx';
    $filePath = storage_path("app/public/$fileName");

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

        // Retornar la descarga del archivo usando el trait WithFileDownloads
        return response()->download($filePath)->deleteFileAfterSend(true);
    }





public function mandarAVentanilla()
{
    if (count($this->selectedAdmisiones) > 0) {
        $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();

        foreach ($admisiones as $admision) {
            // Cambiar el estado a 9
            $admision->estado = 9;
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

}
