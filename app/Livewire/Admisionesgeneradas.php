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



class Admisionesgeneradas extends Component
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
    public $origen;



    public function render()
{
    // Obtener la ciudad y el nombre del usuario autenticado
    $userCity = Auth::user()->city;
    $userName = Auth::user()->name;

    // Filtrar y paginar los registros
    $admisiones = Admision::where('origen', $userCity) // Filtrar por la ciudad del usuario
        ->where('creacionadmision', $userName) // Filtrar por el nombre del usuario creador
        ->where('codigo', 'like', '%' . $this->searchTerm . '%') // Filtro por código
        ->orderBy('fecha', 'desc') // Ordenar por fecha
        ->paginate($this->perPage);

    // Guardar los IDs de la página actual
    $this->currentPageIds = $admisiones->pluck('id')->toArray();

    return view('livewire.admisionesgeneradas', [
        'admisiones' => $admisiones,
    ]);
}

public function exportToExcel()
{
    $user = Auth::user(); // Obtener el usuario logueado
    $userName = $user->name; // Obtener el nombre del usuario logueado

    // Filtrar admisiones según la ciudad del usuario y el nombre del creador
    $admisiones = Admision::where('origen', $user->city) // Filtrar según la ciudad del usuario
        ->where('creacionadmision', $userName) // Filtrar por el nombre del usuario creador
        ->where('codigo', 'like', '%' . $this->searchTerm . '%')
        ->orderBy('fecha', 'desc')
        ->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Inserta imagen de cabecera
    $drawing = new Drawing();
    $drawing->setPath(public_path('images/CABECERA.jpg')); // Cambia al nombre correcto
    $drawing->setCoordinates('D1'); // Movida 3 columnas a la derecha
    $drawing->setHeight(80);
    $drawing->setWorksheet($sheet);

    // Configuración de títulos principales
    $sheet->mergeCells('B8:D8');
    $sheet->setCellValue('B8', 'KARDEX DIARIO DE RENDICIÓN');
    $sheet->mergeCells('B9:D9');
    $sheet->setCellValue('B9', 'AGENCIA BOLIVIANA DE CORREOS');
    $sheet->mergeCells('B10:D10');
    $sheet->setCellValue('B10', 'EXPRESADO EN BS.');

    // Dirección de Operaciones
    $sheet->mergeCells('H8:J8');
    $sheet->setCellValue('H8', 'Dirección de Operaciones');
    $sheet->mergeCells('H9:J9');
    $sheet->setCellValue('H9', 'Admisión');
    $sheet->mergeCells('H10:J10');
    $sheet->setCellValue('H10', 'Kardex 1');

    // Información de encabezado
    $sheet->setCellValue('A12', 'Oficina Postal:');
    $sheet->setCellValue('B12', $user->city); // Origen dinámico del usuario logueado
    $sheet->setCellValue('D12', 'Nombre Responsable:');
    $sheet->setCellValue('E12', $user->name); // Nombre del usuario logueado
    $sheet->setCellValue('G12', 'Fecha de Recaudación:');
    $sheet->setCellValue('H12', Carbon::now()->format('d/m/Y'));

    $sheet->setCellValue('A13', 'Ventanilla:');
    $sheet->setCellValue('B13', '3'); // Cambia según el dato dinámico

    // Encabezados de la tabla
    $headers = [
        'N°', 'FECHA', 'CANTIDAD', 'ORIGEN', 'TIPO DE CORRESPONDENCIA',
        'CÓDIGO DE ENVÍO', 'PESO', 'PAIS/CIUDAD DE DESTINO', 'N° FACTURA', 'IMPORTE'
    ];
    $sheet->fromArray($headers, null, 'A15');

    // Estilo para el encabezado de la tabla
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4F81BD'], // Color azul
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    // Aplica estilo a los encabezados
    $sheet->getStyle('A15:J15')->applyFromArray($headerStyle);

    // Datos de admisiones
    $row = 16;
    foreach ($admisiones as $index => $admision) {
        $sheet->setCellValue('A' . $row, $index + 1); // Número
        $sheet->setCellValue('B' . $row, $admision->fecha); // Fecha
        $sheet->setCellValue('C' . $row, $admision->cantidad); // Cantidad
        $sheet->setCellValue('D' . $row, $admision->origen); // Origen
        $sheet->setCellValue('E' . $row, 'EMS'); // Tipo de Correspondencia
        $sheet->setCellValue('F' . $row, $admision->codigo); // Código de Envío
        $sheet->setCellValue('G' . $row, $admision->peso); // Peso

        // Ciudad de destino
        $ciudad = $admision->ciudad ?? 'SIN DESTINO'; // Valor por defecto si está vacío
        $sheet->setCellValue('H' . $row, $ciudad); // País/Ciudad de Destino

        $sheet->setCellValue('I' . $row, $admision->numero_factura); // N° Factura
        $sheet->setCellValue('J' . $row, $admision->precio); // Importe

        // Aplica bordes para cada fila
        $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $row++;
    }

    // Totales
    $sheet->setCellValue('I' . $row, 'TOTAL PARCIAL:');
    $sheet->setCellValue('J' . $row, $admisiones->sum('precio'));
    $row++;
    $sheet->setCellValue('I' . $row, 'TOTAL GENERAL:');
    $sheet->setCellValue('J' . $row, $admisiones->sum('precio'));

    // Ajustar anchos de columnas
    $sheet->getColumnDimension('A')->setWidth(20); // N°
    $sheet->getColumnDimension('B')->setWidth(20); // FECHA
    $sheet->getColumnDimension('C')->setWidth(10); // CANTIDAD
    $sheet->getColumnDimension('D')->setWidth(20); // ORIGEN
    $sheet->getColumnDimension('E')->setWidth(25); // TIPO DE CORRESPONDENCIA
    $sheet->getColumnDimension('F')->setWidth(30); // CÓDIGO DE ENVÍO
    $sheet->getColumnDimension('G')->setWidth(20); // PESO
    $sheet->getColumnDimension('H')->setWidth(25); // PAIS/CIUDAD DE DESTINO
    $sheet->getColumnDimension('I')->setWidth(15); // N° FACTURA
    $sheet->getColumnDimension('J')->setWidth(15); // IMPORTE

    // Guardar archivo temporal
    $fileName = 'Reporte_Admisiones_Kardex.xlsx';
    $filePath = storage_path('app/public/' . $fileName);
    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    // Retornar descarga
    return response()->download($filePath)->deleteFileAfterSend(true);
}




    
    

    
}
