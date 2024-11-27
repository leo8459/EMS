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
        // Obtener la ciudad del usuario autenticado
        $userCity = Auth::user()->city;
    
        // Filtrar y paginar los registros
        $admisiones = Admision::where('origen', $userCity) // Filtrar por la ciudad del usuario
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
        $admisiones = Admision::where('origen', $user->city) // Filtrar según la ciudad del usuario
            ->where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->orderBy('fecha', 'desc')
            ->get();
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Inserta imagen de cabecera
        $drawing = new Drawing();
        $drawing->setPath(public_path('images/CABECERA.jpg')); // Cambia al nombre correcto
        $drawing->setCoordinates('A1');
        $drawing->setHeight(80);
        $drawing->setWorksheet($sheet);
    
        // Configuración de títulos principales
        $sheet->mergeCells('B4:D4');
        $sheet->setCellValue('B4', 'KARDEX DIARIO DE RENDICIÓN');
        $sheet->mergeCells('B5:D5');
        $sheet->setCellValue('B5', 'AGENCIA BOLIVIANA DE CORREOS');
        $sheet->mergeCells('B6:D6');
        $sheet->setCellValue('B6', 'EXPRESADO EN BS.');
    
        // Dirección de Operaciones
        $sheet->mergeCells('H4:J4');
        $sheet->setCellValue('H4', 'Dirección de Operaciones');
        $sheet->mergeCells('H5:J5');
        $sheet->setCellValue('H5', 'Admisión');
        $sheet->mergeCells('H6:J6');
        $sheet->setCellValue('H6', 'Kardex 1');
    
        // Información de encabezado
        $sheet->setCellValue('A8', 'Oficina Postal:');
        $sheet->setCellValue('B8', $user->city); // Origen dinámico del usuario logueado
        $sheet->setCellValue('D8', 'Nombre Responsable:');
        $sheet->setCellValue('E8', $user->name); // Nombre del usuario logueado
        $sheet->setCellValue('G8', 'Fecha de Recaudación:');
        $sheet->setCellValue('H8', Carbon::now()->format('d/m/Y'));
    
        $sheet->setCellValue('A9', 'Ventanilla:');
        $sheet->setCellValue('B9', '3'); // Cambia según el dato dinámico
    
        // Encabezados de la tabla
        $headers = [
            'N°', 'FECHA', 'CANTIDAD', 'ORIGEN', 'CIUDAD',
            'CÓDIGO DE ENVÍO', 'PESO', 'PAIS/CIUDAD DE DESTINO', 'N° FACTURA', 'IMPORTE'
        ];
        $sheet->fromArray($headers, null, 'A11');
    
        // Datos de admisiones
        $row = 12;
        foreach ($admisiones as $index => $admision) {
            $sheet->setCellValue('A' . $row, $index + 1); // Número
            $sheet->setCellValue('B' . $row, $admision->fecha); // Fecha
            $sheet->setCellValue('C' . $row, $admision->cantidad); // Cantidad
            $sheet->setCellValue('D' . $row, $admision->origen); // Origen
            $sheet->setCellValue('E' . $row, isset($admision->ciudad) ? $admision->ciudad : 'SIN CIUDAD');
            $sheet->setCellValue('F' . $row, $admision->codigo); // Código de Envío
            $sheet->setCellValue('G' . $row, $admision->peso); // Peso
            $sheet->setCellValue('H' . $row, $admision->ciudad_destino); // País/Ciudad de Destino
            $sheet->setCellValue('I' . $row, $admision->numero_factura); // N° Factura
            $sheet->setCellValue('J' . $row, $admision->precio); // Importe
            $row++;
        }
    
        // Totales
        $sheet->setCellValue('I' . $row, 'TOTAL PARCIAL:');
        $sheet->setCellValue('J' . $row, $admisiones->sum('precio'));
        $row++;
        $sheet->setCellValue('I' . $row, 'TOTAL GENERAL:');
        $sheet->setCellValue('J' . $row, $admisiones->sum('precio'));
    
        // Ajustes de estilo
        $sheet->getStyle('B4:D6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('H4:J6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A11:J11')->getFont()->setBold(true);
        $sheet->getStyle('B4:J6')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(10);
    
        // Guardar archivo temporal
        $fileName = 'Reporte_Admisiones_Kardex.xlsx';
        $filePath = storage_path('app/public/' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    
        // Retornar descarga
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
    
    

    
}
