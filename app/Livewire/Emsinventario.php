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
use App\Models\Eventos;
use Livewire\WithFileDownloads;
use App\Models\Historico;
use Illuminate\Support\Facades\Http;

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
    public $selectedDepartment = null;
    public $lastSearchTerm = '';
    public $selectedCity = null;
    public $cityJustUpdated = false;
    public $showEditModal = false;
    public $editAdmisionId = null;
    public $editDireccion = '';
    public $manualManifiesto = '';
    public $useManualManifiesto = false;
    public $currentManifiesto = null;
    public $selectedTransport = 'AEREO';
    public $numeroVuelo = '';
    public $selectedAdmisionesList = [];
    public $showCN33Modal = false;
    public $selectedSolicitudesExternas = [];

    public $origen, $fecha, $servicio, $tipo_correspondencia, $cantidad, $peso, $destino, $codigo, $precio, $numero_factura, $nombre_remitente, $nombre_envia, $carnet, $telefono_remitente, $nombre_destinatario, $telefono_destinatario, $direccion, $ciudad, $pais, $provincia, $contenido;

    public $showReprintModal = false;
    public $inputManifiesto = '';

    public $showReimprimirModal = false;
    public $manifiestoInput = '';
    public $solicitudesExternas = [];

    public $selectedRecords = [];
    public $showContratoModal = false;
    public $contratoCodigo = '';
    public $contratoPeso = '';
    public $contratoObservacion = '';
    public $contratoDestino = '';

    // 👇 NUEVO: Códigos retenidos (estado 12) para la alerta
    public $retenidosCodigos = [];

    protected $paginationTheme = 'bootstrap';

    public function updatedSelectedCity()
    {
        $this->resetPage();
        $this->cityJustUpdated = true;
    }

    public function render()
    {
        $userCity = Auth::user()->city;

        // 1) Admisiones internas visibles (tus condiciones)
        $admisiones = Admision::query()
            ->where(function ($query) use ($userCity) {
                $query->where(function ($subQuery) use ($userCity) {
                    $subQuery->where('estado', 7)
                        ->where(function ($innerQuery) use ($userCity) {
                            $innerQuery->where('reencaminamiento', $userCity)
                                ->orWhere(function ($orQuery) use ($userCity) {
                                    $orQuery->whereNull('reencaminamiento')
                                        ->where('ciudad', $userCity);
                                });
                        });
                })
                ->orWhere(function ($subQuery) use ($userCity) {
                    $subQuery->where('estado', 3)
                        ->where('origen', $userCity);
                })
                ->orWhere(function ($subQuery) use ($userCity) {
                    $subQuery->where('estado', 10)
                        ->where(function ($innerQuery) use ($userCity) {
                            $innerQuery->where('reencaminamiento', $userCity)
                                ->orWhere(function ($orQuery) use ($userCity) {
                                    $orQuery->whereNull('reencaminamiento')
                                        ->where('ciudad', $userCity);
                                });
                        });
                });
            })
            ->orderBy('fecha', 'desc')
            ->paginate($this->perPage);

        // 2) Solicitudes externas (API, estado=5)
        $response = Http::get('http://172.65.10.52:8450/api/ems/estado/5');
        if ($response->successful()) {
            $solicitudesExternas = collect($response->json());
        } else {
            $solicitudesExternas = collect();
        }

        // Filtro opcional por ciudad
        if ($this->selectedCity) {
            $filtered = collect($admisiones->items())->filter(function ($adm) {
                return $adm->ciudad === $this->selectedCity
                    || $adm->reencaminamiento === $this->selectedCity;
            });
            $admisiones = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered,
                $filtered->count(),
                $this->perPage,
                $admisiones->currentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        // 👇 NUEVO: Buscar paquetes retenidos (estado 12) para la ALERTA
        // Se consideran retenidos en tu ámbito si:
        //  - reencaminamiento == tu ciudad, o
        //  - (reencaminamiento es null y ciudad == tu ciudad), o
        //  - origen == tu ciudad  (para no perder casos creados localmente)
        $this->retenidosCodigos = Admision::query()
            ->where('estado', 12)
            ->where(function ($q) use ($userCity) {
                $q->where('reencaminamiento', $userCity)
                  ->orWhere(function ($or) use ($userCity) {
                      $or->whereNull('reencaminamiento')->where('ciudad', $userCity);
                  })
                  ->orWhere('origen', $userCity);
            })
            ->orderBy('fecha', 'desc')
            ->pluck('codigo')
            ->toArray();

        return view('livewire.emsinventario', [
            'admisiones' => $admisiones,
            'solicitudesExternas' => $solicitudesExternas,
            'retenidosCodigos' => $this->retenidosCodigos, // 👈 pasar a la vista
        ]);
    }

    public function search()
    {
        $userCity = Auth::user()->city;

        // === 1) Admisiones internas + filtro por destino ===
        $baseQuery = Admision::query()
            ->where(function ($query) use ($userCity) {
                $query->where(function ($subQuery) use ($userCity) {
                    $subQuery->where('estado', 7)
                        ->where(function ($innerQuery) use ($userCity) {
                            $innerQuery->where('reencaminamiento', $userCity)
                                ->orWhere(function ($orQuery) use ($userCity) {
                                    $orQuery->whereNull('reencaminamiento')
                                        ->where('ciudad', $userCity);
                                });
                        });
                })
                ->orWhere(function ($subQuery) use ($userCity) {
                    $subQuery->where('estado', 3)
                        ->where('origen', $userCity);
                })
                ->orWhere(function ($subQuery) use ($userCity) {
                    $subQuery->where('estado', 10)
                        ->where(function ($innerQuery) use ($userCity) {
                            $innerQuery->where('reencaminamiento', $userCity)
                                ->orWhere(function ($orQuery) use ($userCity) {
                                    $orQuery->whereNull('reencaminamiento')
                                        ->where('ciudad', $userCity);
                                });
                        });
                });
            });

        if ($this->selectedCity) {
            $baseQuery->where(function ($q) {
                $q->where('ciudad', $this->selectedCity)
                  ->orWhere('reencaminamiento', $this->selectedCity);
            });
        }

        if (trim($this->searchTerm) !== '') {
            $baseQuery->where('codigo', 'like', '%' . $this->searchTerm . '%');
        }

        $foundIds = $baseQuery->pluck('id')->toArray();

        $this->selectedAdmisiones = array_values(array_unique(
            array_merge($this->selectedAdmisiones, $foundIds)
        ));

        // === 2) Externas (solo destino) ===
        $response = Http::get('http://172.65.10.52:8450/api/ems/estado/5');
        if ($response->successful()) {
            $externas = collect($response->json());

            if ($this->selectedCity) {
                $map = [
                    'LPB' => 'LA PAZ',
                    'SRZ' => 'SANTA CRUZ',
                    'CBB' => 'COCHABAMBA',
                    'ORU' => 'ORURO',
                    'POI' => 'POTOSI',
                    'TJA' => 'TARIJA',
                    'SRE' => 'CHUQUISACA',
                    'TDD' => 'BENI',
                    'CIJ' => 'PANDO',
                ];

                $externas = $externas->filter(function ($item) use ($map) {
                    $guia = $item['guia'] ?? '';
                    $right = strtoupper(substr($guia, 7, 3)); // DESTINO
                    $destino = $map[$right] ?? null;
                    return $destino === $this->selectedCity;
                });
            }

            if (trim($this->searchTerm) !== '') {
                $externas = $externas->filter(function ($item) {
                    return stripos($item['guia'] ?? '', $this->searchTerm) !== false;
                });
            }

            $this->selectedSolicitudesExternas = array_values(array_unique(
                array_merge($this->selectedSolicitudesExternas, $externas->pluck('guia')->toArray())
            ));
        }

        $this->searchTerm = '';
    }

    public function toggleSelectAll()
    {
        $this->selectAll = !$this->selectAll;

        if ($this->selectAll) {
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

            $this->selectedAdmisiones = array_unique(array_merge($this->selectedAdmisiones, $visibleAdmisiones));
        } else {
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
        if (count($this->selectedAdmisiones) > 0 || count($this->selectedSolicitudesExternas) > 0) {
            $admissions = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            if ($admissions->isEmpty() && empty($this->selectedSolicitudesExternas)) {
                session()->flash('error', 'No hay admisiones seleccionadas.');
                return;
            }

            $response = Http::get('http://172.65.10.52:8450/api/ems/estado/5');

            if ($response->successful()) {
                $solicitudes = collect($response->json());
                $this->solicitudesExternas = $solicitudes->whereIn('guia', $this->selectedSolicitudesExternas)->values()->toArray();
            } else {
                $this->solicitudesExternas = [];
                session()->flash('error', 'No se pudo obtener datos de la API.');
            }

            $this->showModal = true;
            $this->destinoModal = null;
            $this->ciudadModal = null;
            $this->selectedAdmisionesCodes = $admissions->pluck('codigo')->toArray();
            $this->selectedAdmisionesList = $admissions;
            $this->selectedDepartment = null;
            $this->manualManifiesto = '';
            $this->selectedTransport = 'AEREO';
            $this->numeroVuelo = '';
        } else {
            session()->flash('error', 'Debe seleccionar al menos una admisión o solicitud externa.');
        }
    }

    public function abrirModalReimprimir()
    {
        $this->showReimprimirModal = true;
    }

    public function mandarAVentanilla()
    {
        if (count($this->selectedAdmisiones) > 0) {
            $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();
            $userCity = Auth::user()->city;
            $admisionesInvalidas = [];

            foreach ($admisiones as $admision) {
                if ($admision->reencaminamiento !== $userCity && $admision->ciudad !== $userCity) {
                    $admisionesInvalidas[] = $admision->codigo;
                }
            }

            if (!empty($admisionesInvalidas)) {
                $admisionesInvalidasStr = implode(', ', $admisionesInvalidas);
                session()->flash('error', "Los siguientes envios no son de tu ciudad para mandarlo a ventanilla: {$admisionesInvalidasStr}");
                return;
            }

            foreach ($admisiones as $admision) {
                $admision->estado = 11;
                $admision->save();

                Eventos::create([
                    'accion' => 'Mandar a ventanilla',
                    'descripcion' => 'La admisión fue enviada a la ventanilla.',
                    'codigo' => $admision->codigo,
                    'user_id' => Auth::id(),
                ]);

                Historico::create([
                    'numero_guia' => $admision->codigo,
                    'fecha_actualizacion' => now(),
                    'id_estado_actualizacion' => 4,
                    'estado_actualizacion' => '"Operador" en posesión del envío',
                ]);
            }

            $this->dispatch('reload-page');
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

            return redirect(request()->header('Referer'));
        } else {
            session()->flash('error', 'No se encontró la admisión para actualizar.');
        }
    }

    public function mandarARegional()
    {
        if (empty($this->selectedAdmisiones) && empty($this->selectedSolicitudesExternas)) {
            session()->flash('error', 'Debe seleccionar al menos una admisión o solicitud externa.');
            return;
        }

        if (empty($this->selectedDepartment)) {
            session()->flash('error', 'Debe seleccionar un departamento para reencaminar.');
            return;
        }

        $cityCodes = [
            'LA PAZ'       => 'LPB',
            'SANTA CRUZ'   => 'SRZ',
            'COCHABAMBA'   => 'CBB',
            'ORURO'        => 'ORU',
            'POTOSI'       => 'PTI',
            'TARIJA'       => 'TJA',
            'CHUQUISACA'   => 'SRE',
            'BENI'         => 'TDD',
            'PANDO'        => 'CIJ',
        ];

        $reencaminamientoAbreviado = $cityCodes[$this->selectedDepartment] ?? $this->selectedDepartment;
        $reencaminamientoCompleto = $this->selectedDepartment;

        if (empty($this->manualManifiesto)) {
            $this->manualManifiesto = $this->generarManifiesto(Auth::user()->city);
        }

        $errores = [];

        if (!empty($this->selectedSolicitudesExternas)) {
            foreach ($this->selectedSolicitudesExternas as $guia) {
                $response = Http::put("http://172.65.10.52:8450/api/solicitudes/reencaminar", [
                    'guia' => $guia,
                    'reencaminamiento' => $reencaminamientoAbreviado,
                    'manifiesto' => $this->manualManifiesto,
                ]);

                if (!$response->successful()) {
                    $errores[] = "Error en la solicitud externa {$guia}: " . $response->body();
                }
            }
        }

        if (!empty($this->selectedAdmisiones)) {
            $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            foreach ($admisiones as $admision) {
                $admision->estado           = 6;
                $admision->manifiesto       = $this->manualManifiesto;
                $admision->reencaminamiento = $reencaminamientoCompleto;
                $admision->tipo_transporte  = $this->selectedTransport;
                $admision->numero_vuelo     = $this->numeroVuelo;
                $admision->save();

                Eventos::create([
                    'accion'      => 'Mandar a regional',
                    'descripcion' => "La admisión fue enviada a la regional con el manifiesto {$this->manualManifiesto}.",
                    'codigo'      => $admision->codigo,
                    'user_id'     => Auth::id(),
                ]);

                Historico::create([
                    'numero_guia'             => $admision->codigo,
                    'fecha_actualizacion'     => now(),
                    'id_estado_actualizacion' => 2,
                    'estado_actualizacion'    => 'En Tránsito',
                ]);
            }
        }

        $solicitudesExternasSeleccionadas = collect($this->solicitudesExternas)
            ->whereIn('guia', $this->selectedSolicitudesExternas);

        $this->selectedAdmisionesList = $admisiones ?? collect();
        return $this->generarPdf($solicitudesExternasSeleccionadas);

        $this->selectedAdmisiones = [];
        $this->selectedSolicitudesExternas = [];
        $this->dispatch('reloadPage');

        if (!empty($errores)) {
            session()->flash('error', implode(', ', $errores));
        } else {
            session()->flash('message', 'Las solicitudes externas han sido reencaminadas y las admisiones enviadas correctamente.');
        }
    }

    private function generarManifiesto($city)
    {
        return \DB::transaction(function () use ($city) {
            $prefix = $this->cityPrefixes[$city] ?? '9';

            $ultimoManifiesto = Admision::where('manifiesto', 'like', "BO$prefix%")
                ->orderBy('manifiesto', 'desc')
                ->value('manifiesto');

            $nuevoNumero = $ultimoManifiesto ? (int) substr($ultimoManifiesto, 3) + 1 : 1;

            return 'BO' . $prefix . str_pad($nuevoNumero, 7, '0', STR_PAD_LEFT);
        });
    }

    public function mount()
    {
        $response = Http::get('http://172.65.10.52:8450/api/ems/estado/5');

        if ($response->successful()) {
            $this->solicitudesExternas = $response->json();
        } else {
            $this->solicitudesExternas = [];
        }

        $this->origen = Auth::user()->city;
        $this->ciudad = "";
        $this->cantidad = 1;
    }

    public function store()
    {
        $this->precio = $this->precio ?? 0;
        $this->fecha = now();

        if (empty($this->codigo)) {
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

        $qrLink = 'https://correos.gob.bo:8000/';

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
            'qrLink' => $qrLink,
            'contenido' => $admision->contenido,
        ];

        $pdf = Pdf::loadView('pdfs.admision', $data);
        $this->dispatch('reloadPage');

        return response()->streamDownload(
            fn() => print($pdf->stream('admision.pdf')),
            'admision.pdf'
        );

        session()->flash('message', 'Admisión creada exitosamente.');
    }

    public function reimprimirManifiesto()
    {
        if (empty($this->manifiestoInput)) {
            $this->manifiestoInput = $this->generarManifiesto(Auth::user()->city);
        }

        $admisionesExistentes = Admision::where('manifiesto', $this->manifiestoInput)->get();

        $response = Http::get("http://172.65.10.52:8450/api/solicitudes/manifiesto/{$this->manifiestoInput}");
        $solicitudesExternasSeleccionadas = collect();

        if ($response->successful()) {
            $data = $response->json();
            $solicitudesExternasSeleccionadas = collect($data);
        }

        if ($admisionesExistentes->isEmpty() && $solicitudesExternasSeleccionadas->isEmpty()) {
            session()->flash('error', 'No se encontraron admisiones con el manifiesto ingresado en ningún sistema.');
            return;
        }

        $this->manualManifiesto = $this->manifiestoInput;
        $this->currentManifiesto = $this->manifiestoInput;
        $this->selectedAdmisionesList = $admisionesExistentes;

        return $this->generarPdf($solicitudesExternasSeleccionadas);
    }

    public function generarPdf($solicitudesExternasSeleccionadas)
    {
        $admisionesSeleccionadas = $this->selectedAdmisionesList;

        if (is_array($solicitudesExternasSeleccionadas)) {
            $solicitudesExternasSeleccionadas = collect($solicitudesExternasSeleccionadas);
        }

        if ($admisionesSeleccionadas->isEmpty() && $solicitudesExternasSeleccionadas->isEmpty()) {
            session()->flash('error', 'No hay admisiones válidas para generar el PDF.');
            return;
        }

        $totalCantidad = count($admisionesSeleccionadas) + count($solicitudesExternasSeleccionadas);

        $totalPeso = 0;
        foreach ($admisionesSeleccionadas as $admision) {
            $peso = (float) ($admision->peso_ems ?? $admision->peso ?? 0);
            $totalPeso += $peso;
        }
        foreach ($solicitudesExternasSeleccionadas as $solicitud) {
            $pesoExterno = (float) ($solicitud['peso_o'] ?? $solicitud['peso_v'] ?? $solicitud['peso_r'] ?? 0);
            $totalPeso += $pesoExterno;
        }

        $data = [
            'admisiones'          => $admisionesSeleccionadas,
            'solicitudesExternas' => $solicitudesExternasSeleccionadas,
            'currentDate'         => now()->format('d/m/Y'),
            'currentTime'         => now()->format('H:i'),
            'currentManifiesto'   => $this->manualManifiesto,
            'loggedInUserCity'    => Auth::user()->city,
            'destinationCity'     => $this->selectedDepartment ?? ($admisionesSeleccionadas->first()->reencaminamiento ?? $admisionesSeleccionadas->first()->ciudad ?? ''),
            'selectedTransport'   => $this->selectedTransport,
            'numeroVuelo'         => $this->numeroVuelo,
            'totalCantidad'       => $totalCantidad,
            'totalPeso'           => number_format($totalPeso, 2, '.', ''),
        ];

        $pdf = Pdf::loadView('pdfs.cn33', $data)->setPaper('letter', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->stream('cn-33.pdf')),
            'cn-33.pdf'
        );
    }

    public function updatedSelectedAdmisiones()
    {
        $this->selectedAdmisionesList = Admision::whereIn('id', $this->selectedAdmisiones)->get();
    }

    public function removeFromSelection($admisionId)
    {
        $this->selectedAdmisiones = array_diff($this->selectedAdmisiones, [$admisionId]);
        $this->updatedSelectedAdmisiones();
    }

    public function abrirModalCN33()
    {
        if (count($this->selectedAdmisiones) > 0 || count($this->selectedSolicitudesExternas) > 0) {
            $this->showCN33Modal = true;
        } else {
            session()->flash('error', 'Debe seleccionar al menos una admisión o solicitud externa.');
        }
    }

    public function añadirACN33()
    {
        if (empty($this->selectedAdmisiones) && empty($this->selectedSolicitudesExternas)) {
            session()->flash('error', 'Debe seleccionar al menos una admisión o solicitud externa.');
            return;
        }

        if (empty($this->manualManifiesto)) {
            $this->manualManifiesto = $this->generarManifiesto(Auth::user()->city);
        }

        if (!empty($this->selectedAdmisiones)) {
            $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            foreach ($admisiones as $admision) {
                $admision->estado = 6;
                $admision->manifiesto = $this->manualManifiesto;
                $admision->save();

                Eventos::create([
                    'accion'      => 'Añadir a CN-33',
                    'descripcion' => "Se añadió al manifiesto {$this->manualManifiesto}.",
                    'codigo'      => $admision->codigo,
                    'user_id'     => Auth::id(),
                ]);
            }
        }

        $errores = [];

        if (!empty($this->selectedSolicitudesExternas)) {
            foreach ($this->selectedSolicitudesExternas as $guia) {
                $response = Http::put("http://172.65.10.52:8450/api/solicitudes/reencaminar", [
                    'guia'       => $guia,
                    'manifiesto' => $this->manualManifiesto,
                    'estado'     => 8,
                ]);

                if (!$response->successful()) {
                    $errores[] = "Error en solicitud externa {$guia}: " . $response->body();
                }
            }
        }

        $this->selectedAdmisiones = [];
        $this->selectedSolicitudesExternas = [];

        $this->dispatch('reloadPage');

        if (!empty($errores)) {
            session()->flash('error', implode(', ', $errores));
        } else {
            session()->flash('message', 'Las admisiones y solicitudes externas han sido añadidas a CN-33 correctamente.');
        }
    }

    public function abrirModalContrato()
    {
        $this->resetContratoFields();
        $this->showContratoModal = true;
    }

    private function resetContratoFields()
    {
        $this->contratoCodigo = '';
        $this->contratoPeso = '';
        $this->contratoObservacion = '';
        $this->contratoDestino = '';
    }

    public function generarContrato()
    {
        $this->validate([
            'contratoCodigo'      => 'required|string',
            'contratoPeso'        => 'required|numeric|min:0.001',
            'contratoDestino'     => 'required|in:LA PAZ,POTOSI,ORURO,SANTA CRUZ,CHUQUISACA,COCHABAMBA,BENI,PANDO,TARIJA',
            'contratoObservacion' => 'nullable|string|max:500',
        ], [
            'contratoCodigo.required'  => 'Debe ingresar un código.',
            'contratoPeso.required'    => 'Debe ingresar un peso.',
            'contratoPeso.numeric'     => 'El peso debe ser numérico.',
            'contratoPeso.min'         => 'El peso debe ser mayor a 0.',
            'contratoDestino.required' => 'Debe seleccionar el departamento de destino.',
            'contratoDestino.in'       => 'El departamento de destino no es válido.',
        ]);

        try {
            \DB::beginTransaction();

            $admision = Admision::where('codigo', trim((string)$this->contratoCodigo))->first();

            $peso = (float) $this->contratoPeso;
            $textoObs = trim((string) ($this->contratoObservacion ?? ''));
            $destino = (string) $this->contratoDestino;

            if ($admision) {
                $admision->peso          = $peso;
                $admision->peso_ems      = $peso;
                $admision->peso_regional = $peso;

                $admision->destino       = $destino;
                $admision->ciudad        = $destino; // ciudad = destino

                if ($textoObs !== '') {
                    $admision->observacion = trim(($admision->observacion ? $admision->observacion . ' | ' : '') . $textoObs);
                }

                $admision->save();

                Eventos::create([
                    'accion'      => 'Generar Contrato',
                    'descripcion' => "Actualización por contrato: peso/peso_ems/peso_regional = {$peso}; destino/ciudad = {$destino}.",
                    'codigo'      => $admision->codigo,
                    'user_id'     => Auth::id(),
                ]);

                \DB::commit();

                $this->showContratoModal = false;
                $this->resetContratoFields();
                $this->dispatch('reload-page');
                session()->flash('message', 'Contrato actualizado: pesos, destino y ciudad (destino) guardados.');
                return;
            }

            $tarifaIdDefecto = 1;

            $admision = Admision::create([
                'origen'           => Auth::user()->city ?? 'LA PAZ',
                'fecha'            => now(),
                'servicio'         => 'CONTRATO',
                'cantidad'         => 1,
                'peso'             => $peso,
                'peso_ems'         => $peso,
                'peso_regional'    => $peso,
                'observacion'      => ($textoObs !== '' ? $textoObs : null),
                'codigo'           => trim((string)$this->contratoCodigo),
                'precio'           => 0,
                'destino'          => $destino,
                'ciudad'           => $destino, // ciudad = destino
                'creacionadmision' => Auth::user()->name ?? null,
                'estado'           => 3,
                'tarifa_id'        => $tarifaIdDefecto,
                'user_id'          => Auth::id(),
            ]);

            Eventos::create([
                'accion'      => 'Generar Contrato',
                'descripcion' => "Creación por contrato: peso/peso_ems/peso_regional = {$peso}; destino/ciudad = {$destino}.",
                'codigo'      => $admision->codigo,
                'user_id'     => Auth::id(),
            ]);

            \DB::commit();

            $this->showContratoModal = false;
            $this->resetContratoFields();
            $this->dispatch('reload-page');
            session()->flash('message', 'Contrato creado: registro nuevo con pesos y ciudad (destino) guardados.');
        } catch (\Throwable $e) {
            \DB::rollBack();
            session()->flash('error', 'No se pudo guardar el contrato: ' . $e->getMessage());
        }
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
