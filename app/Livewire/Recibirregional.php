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



class Recibirregional extends Component
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

    public function render()
    {
        // Filtrar y paginar las admisiones en estado 3
        $admisiones = Admision::with('user')
            ->where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->where('estado', 6)
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);
    
        return view('livewire.recibirregional', [
            'admisiones' => $admisiones,
        ]);
    }
}
