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

    

    public function render()
    {
        // Obtener la ciudad del usuario autenticado
        $userCity = Auth::user()->city;
    
        // Si el campo de búsqueda está vacío, mostrar todos los registros
        if (empty($this->searchTerm) && empty($this->lastSearchTerm)) {
            $admisiones = Admision::with('user')
                ->where(function ($query) use ($userCity) {
                    $query->where(function ($subQuery) use ($userCity) {
                        $subQuery->where('estado', 3)
                                 ->where('origen', $userCity)
                                 ->where('ciudad', '!=', $userCity);
                    })
                    ->orWhere(function ($subQuery) use ($userCity) {
                        $subQuery->where('estado', 7)
                                 ->where('ciudad', $userCity)
                                 ->where('origen', '!=', $userCity);
                    });
                })
                ->orderBy('fecha', 'desc')
                ->paginate($this->perPage);
    
            return view('livewire.emsinventario', [
                'admisiones' => $admisiones,
            ]);
        }
    
        // Si hay un término de búsqueda, procesarlo
        if (!empty($this->searchTerm)) {
            $this->lastSearchTerm = $this->searchTerm;
            $this->searchTerm = ''; // Limpia el campo de búsqueda
        }
    
        // Filtrar las admisiones según el término buscado
        $admisiones = Admision::with('user')
            ->where(function ($query) use ($userCity) {
                $query->where(function ($subQuery) use ($userCity) {
                    $subQuery->where('estado', 3)
                             ->where('origen', $userCity)
                             ->where('ciudad', '!=', $userCity);
                })
                ->orWhere(function ($subQuery) use ($userCity) {
                    $subQuery->where('estado', 7)
                             ->where('ciudad', $userCity)
                             ->where('origen', '!=', $userCity);
                });
            })
            ->where('codigo', 'like', '%' . $this->lastSearchTerm . '%') // Usa $lastSearchTerm aquí
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);
    
        $this->currentPageIds = $admisiones->pluck('id')->toArray();
    
        // Agregar automáticamente a los seleccionados si hay un término de búsqueda
        if (!empty($this->lastSearchTerm)) {
            $newSelections = $admisiones->pluck('id')->toArray();
            $this->selectedAdmisiones = array_unique(array_merge($this->selectedAdmisiones, $newSelections));
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
    
            // Obtener las ciudades únicas de las admisiones seleccionadas
            $cities = $admissions->pluck('ciudad')->unique();
    
            if ($cities->count() > 1) {
                // Hay admisiones con ciudades diferentes
                // Encontrar las admisiones que tienen una ciudad diferente a la primera
                $firstCity = $cities->first();
                $differentAdmissions = $admissions->filter(function ($admission) use ($firstCity) {
                    return $admission->ciudad != $firstCity;
                });
    
                // Obtener los códigos y ciudades de las admisiones diferentes
                $differentAdmissionsInfo = $differentAdmissions->map(function ($admission) {
                    return "Código: {$admission->codigo}, Ciudad: {$admission->ciudad}";
                })->implode('; ');
    
                session()->flash('error', 'Hay admisiones que no coinciden en ciudad con las demás: ' . $differentAdmissionsInfo);
                return;
            }
    
            $this->showModal = true;
    
            $this->destinoModal = $admissions->first()->destino ?? null;
            $this->ciudadModal = $admissions->first()->ciudad ?? null;
    
            $this->selectedAdmisionesCodes = $admissions->pluck('codigo')->toArray();
        } else {
            session()->flash('error', 'Debe seleccionar al menos una admisión.');
        }
    }
    


public function mandarARegional()
{
    Admision::whereIn('id', $this->selectedAdmisiones)
        ->where('origen', Auth::user()->city) // Validar ciudad del usuario
        ->update(['estado' => 6]); // Cambiar estado a enviado a regional

    $this->selectedAdmisiones = [];
    $this->showModal = false;

    session()->flash('message', 'Las admisiones seleccionadas se han enviado a la regional.');
}


public function generarExcel()
{
    $admissions = Admision::whereIn('id', $this->selectedAdmisiones)
        ->where('origen', Auth::user()->city) // Validar ciudad del usuario
        ->get();

    if ($admissions->isEmpty()) {
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
        $firstPackage = Admision::find($this->selectedAdmisiones[0]);
    
        // Nombre del usuario logueado
        $loggedInUserName = Auth::user()->name;
    
        // Estilo para encabezado
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['vertical' => 'center', 'horizontal' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ];
    
        // Configurar el ancho de las columnas, omitiendo "ORIG" y "CIUDAD"
        $worksheet->getColumnDimension('A')->setWidth(20); // ENVIO
        $worksheet->getColumnDimension('B')->setWidth(10); // CAN
        $worksheet->getColumnDimension('C')->setWidth(10); // COR
        $worksheet->getColumnDimension('D')->setWidth(15); // EMS
        $worksheet->getColumnDimension('E')->setWidth(25); // CLIENTE
        $worksheet->getColumnDimension('F')->setWidth(20); // ENDAS
        $worksheet->getColumnDimension('G')->setWidth(30); // OBSERVACION
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
        $drawing->setWidth(200);
        $drawing->setWorksheet($worksheet);
    
        // Fila 3: BO-BOLIVIA y Airmails
        $worksheet->mergeCells('A3:C3');
        $worksheet->setCellValue('A3', 'BO-BOLIVIA');
        $worksheet->getStyle('A3')->applyFromArray($headerStyle);
    
        $worksheet->mergeCells('H3:M3');
        $worksheet->setCellValue('H3', 'Airmails');
        $worksheet->getStyle('H3')->applyFromArray($headerStyle);
    
        // Fila 4: Office of origin
        $worksheet->mergeCells('A4:C4');
        $worksheet->setCellValue('A4', 'Office of origin');
        $worksheet->getStyle('A4')->applyFromArray($headerStyle);
    
        $worksheet->mergeCells('H4:M4');
        $worksheet->setCellValue('H4', 'DIA');
        $worksheet->getStyle('H4')->applyFromArray($headerStyle);
    
        // Fila 5: Origen y Fecha actual
        $worksheet->mergeCells('A5:C5');
        $worksheet->setCellValue('A5', $firstPackage->origen ?? '');
        $worksheet->getStyle('A5')->applyFromArray($headerStyle);
    
        $worksheet->mergeCells('H5:M5');
        $worksheet->setCellValue('H5', $currentDate);
        $worksheet->getStyle('H5')->applyFromArray($headerStyle);
    
        // Fila 6 y 7: Office of destination y ciudad
        $worksheet->mergeCells('A6:C6');
        $worksheet->setCellValue('A6', 'office of destination');
        $worksheet->getStyle('A6')->applyFromArray($headerStyle);
    
        $worksheet->mergeCells('A7:C7');
        $worksheet->setCellValue('A7', $firstPackage->ciudad ?? '');
        $worksheet->getStyle('A7')->applyFromArray($headerStyle);
    
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
    
        // Fila 11: Encabezado de columnas, excluyendo ORIG y CIUDAD
        $worksheet->setCellValue('A11', 'ENVIO');
        $worksheet->setCellValue('B11', 'CAN');
        $worksheet->setCellValue('C11', 'COR');
        $worksheet->setCellValue('D11', 'EMS');
        $worksheet->setCellValue('E11', 'CLIENTE');
        $worksheet->setCellValue('F11', 'ENDAS');
        $worksheet->setCellValue('H11', 'OBSERVACION');
        $worksheet->setCellValue('G11', 'OFICIAL'); // Nueva columna

        $worksheet->getStyle('A11:H11')->applyFromArray($headerStyle);
    
        // Agregar los datos de admisiones seleccionadas
        $currentRow = 12;
        $totalCantidad = 0;
        $totalPeso = 0;
    
        foreach ($this->selectedAdmisiones as $admisionId) {
            $admision = Admision::find($admisionId);
            $peso = $admision->peso_ems ?: $admision->peso; // Usa peso_ems o peso si está vacío
    
            $worksheet->setCellValue("A$currentRow", $admision->codigo);
            $worksheet->setCellValue("B$currentRow", 1); // Cantidad fija (1)
            $worksheet->setCellValue("D$currentRow", $peso); // Mostrar el peso (peso_ems o peso)
            $worksheet->setCellValue("E$currentRow", $admision->nombre_remitente);
            $worksheet->setCellValue("H$currentRow", $admision->observacion);
            $worksheet->setCellValue("G$currentRow", ''); // Campo vacío para "OFICIAL"
            $worksheet->getStyle("A$currentRow:H$currentRow")->applyFromArray($headerStyle);
    
            // Acumular cantidad y peso
            $totalCantidad += 1;
            $totalPeso += $peso;
    
            $currentRow++;
        }
    
      // Fila de totales
$totalRow = $currentRow; // Guardar la fila donde están los totales
$worksheet->setCellValue("A$totalRow", 'TOTAL');
$worksheet->setCellValue("B$totalRow", '=SUM(B12:B' . ($currentRow - 1) . ')'); // Fórmula para sumar la columna B
$worksheet->setCellValue("D$totalRow", '=SUM(D12:D' . ($currentRow - 1) . ')'); // Fórmula para sumar la columna D
$worksheet->getStyle("A$totalRow:G$totalRow")->applyFromArray($headerStyle);

    // Agregar información adicional al lado izquierdo en el mismo orden que en la imagen
$currentRow += 2; // Dejar espacio después de la tabla
// Usuario logueado en lugar de "MIRANDA"
$loggedInUserCity = Auth::user()->name;
// Primera línea: Dispatching office of exchange y la ciudad del usuario
$worksheet->setCellValue("A$currentRow", 'Dispatching office of exchange');
$currentRow++;
$worksheet->setCellValue("A$currentRow", $loggedInUserCity); // Ciudad del usuario logueado (reemplaza "MIRANDA")
$currentRow++;
$worksheet->setCellValue("A$currentRow", 'Signature');
$currentRow++;
$worksheet->setCellValue("A$currentRow", '______________________'); // Espacio para la firma
$currentRow++;

// Segunda línea: Salidas internacionales
$worksheet->setCellValue("A$currentRow", 'Salidas Internacionales');
$currentRow += 2; // Dejar espacio

// Datos a la derecha, colocados al mismo nivel que en la imagen
$worksheet->setCellValue("D" . ($currentRow - 6), 'The official of the carrier of airport');
$worksheet->setCellValue("D" . ($currentRow - 5), 'Date and signature');
$currentRow++;

$worksheet->setCellValue("F" . ($currentRow - 6), 'Office of exchange of destination');
$worksheet->setCellValue("F" . ($currentRow - 5), 'Date and signature');

// Aplicar estilos para mantener consistencia
$worksheet->getStyle("A" . ($currentRow - 10) . ":F$currentRow")->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['vertical' => 'center', 'horizontal' => 'left'],
]);


        // Guardar el archivo en el servidor temporalmente y luego enviarlo como descarga
        $fileName = 'designado_operador_postal.xlsx';
        $filePath = storage_path("app/public/$fileName");
    
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
    public function abrirReencaminamientoModal()
{
    if (count($this->selectedAdmisiones) > 0) {
        $admissions = Admision::whereIn('id', $this->selectedAdmisiones)->get();
        if ($admissions->isEmpty()) {
            session()->flash('error', 'No hay admisiones válidas seleccionadas.');
            return;
        }
        $this->selectedAdmisionesCodes = $admissions->pluck('codigo')->toArray();
        $this->showReencaminamientoModal = true;
    } else {
        session()->flash('error', 'Debe seleccionar al menos una admisión.');
    }
}

public function reencaminar()
{
    if ($this->selectedDepartment) {
        Admision::whereIn('id', $this->selectedAdmisiones)
            ->update([
                'reencaminamiento' => $this->selectedDepartment,
                'estado' => 8
            ]);
        
        $this->selectedAdmisiones = [];
        $this->showReencaminamientoModal = false;
        $this->selectedDepartment = null;
        
        session()->flash('message', 'Las admisiones seleccionadas han sido reencaminadas.');
    } else {
        session()->flash('error', 'Debe seleccionar un departamento.');
    }
}
    
}
