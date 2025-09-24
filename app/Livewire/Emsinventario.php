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
use App\Models\Historico; // Asegúrate de importar el modelo Evento
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
    public $selectedAdmisionesList = []; // Lista de admisiones seleccionadas
    public $showCN33Modal = false; // Controla la visibilidad del modal para CN-33
    public $selectedSolicitudesExternas = [];   // Para registros externos

    public $origen, $fecha, $servicio, $tipo_correspondencia, $cantidad, $peso, $destino, $codigo, $precio, $numero_factura, $nombre_remitente, $nombre_envia, $carnet, $telefono_remitente, $nombre_destinatario, $telefono_destinatario, $direccion, $ciudad, $pais, $provincia, $contenido;

    public $showReprintModal = false; // Controla la visibilidad del modal de reimpresión
    public $inputManifiesto = ''; // Almacena el manifiesto ingresado para la búsqueda

    public $showReimprimirModal = false;
    public $manifiestoInput = '';
    public $solicitudesExternas = [];
    // Dentro de tu clase Livewire:
    public $selectedRecords = []; // Aquí se guardarán los identificadores unificados
public $showContratoModal = false;
public $contratoCodigo = '';
public $contratoPeso = '';
public $contratoObservacion = '';





    public function updatedSelectedCity()
    {
        $this->resetPage(); // Reseteamos la paginación a la página 1
        $this->cityJustUpdated = true; // Indicamos que la ciudad ha sido actualizada
    }

    public function render()
    {
        $userCity = Auth::user()->city;

        // 1) Admisiones internas (estado=3|7|10 con tu condición original):
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

        // 2) Solicitudes externas (por API):
        //    Para la tabla, siempre traigo las de estado=5, 
        //    igual que tu ejemplo:
        $response = Http::get('http://172.65.10.52:8450/api/ems/estado/5');
        if ($response->successful()) {
            $solicitudesExternas = collect($response->json());
        } else {
            $solicitudesExternas = collect();
        }

        // (Opcional) Filtrar por selectedCity, etc.
        if ($this->selectedCity) {
            // Ejemplo: filtrar las admisiones "paginadas" en memoria
            $filtered = collect($admisiones->items())->filter(function ($adm) {
                return $adm->ciudad === $this->selectedCity
                    || $adm->reencaminamiento === $this->selectedCity;
            });
            // Rearmar la paginación con la sub-colección (un truco)
            $admisiones = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered,
                $filtered->count(),
                $this->perPage,
                $admisiones->currentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return view('livewire.emsinventario', [
            'admisiones' => $admisiones,
            'solicitudesExternas' => $solicitudesExternas,
        ]);
    }

    public function search()
    {
        // 1) Buscar ADMISIÓN (interno)
        $admisionesEncontradas = Admision::where('codigo', 'like', '%' . $this->searchTerm . '%')
            ->orderBy('fecha', 'desc')
            ->get(); // O ->paginate(...) si deseas.

        // Obtener IDs encontrados
        $foundIds = $admisionesEncontradas->pluck('id')->toArray();

        // Unir con los ya seleccionados sin duplicar
        $this->selectedAdmisiones = array_unique(
            array_merge($this->selectedAdmisiones, $foundIds)
        );

        // 2) Buscar en API EXTERNA
        $response = Http::get('http://172.65.10.52:8450/api/ems/estado/5');
        if ($response->successful()) {
            // Convertimos a colección
            $allExternas = collect($response->json());

            // Filtramos
            $solicitudesExternasEncontradas = $allExternas->filter(function ($item) {
                // stripos -> busca sin importar mayúsc/minúsc
                return stripos($item['guia'], $this->searchTerm) !== false;
            })->values();

            // Obtenemos las guías encontradas
            $foundGuias = $solicitudesExternasEncontradas->pluck('guia')->toArray();

            // Unir con lo que ya estaba seleccionado
            $this->selectedSolicitudesExternas = array_unique(
                array_merge($this->selectedSolicitudesExternas, $foundGuias)
            );
        }

        // 3) Limpiar searchTerm para que el usuario pueda digitar otra búsqueda
        $this->searchTerm = '';
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
        if (count($this->selectedAdmisiones) > 0 || count($this->selectedSolicitudesExternas) > 0) {
            // Obtener solo las admisiones seleccionadas
            $admissions = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            if ($admissions->isEmpty() && empty($this->selectedSolicitudesExternas)) {
                session()->flash('error', 'No hay admisiones seleccionadas.');
                return;
            }

            // Obtener datos de la API solo de los registros seleccionados
            $response = Http::get('http://172.65.10.52:8450/api/ems/estado/5');

            if ($response->successful()) {
                $solicitudes = collect($response->json()); // Convertir en colección para filtrar
                // Filtrar solo los registros seleccionados
                $this->solicitudesExternas = $solicitudes->whereIn('guia', $this->selectedSolicitudesExternas)->values()->toArray();
            } else {
                $this->solicitudesExternas = [];
                session()->flash('error', 'No se pudo obtener datos de la API.');
            }

            // Guardamos los datos en variables que el modal usará
            $this->showModal = true;
            $this->destinoModal = null;
            $this->ciudadModal = null;
            $this->selectedAdmisionesCodes = $admissions->pluck('codigo')->toArray();
            $this->selectedAdmisionesList = $admissions; // Guardar la lista para mostrar en el modal
            $this->selectedDepartment = null;
            $this->manualManifiesto = ''; // Resetear manifiesto si es necesario
            $this->selectedTransport = 'AEREO'; // Default transporte
            $this->numeroVuelo = ''; // Resetear el número de vuelo
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
            $userCity = Auth::user()->city; // Ciudad del usuario logueado
            $admisionesInvalidas = [];

            foreach ($admisiones as $admision) {
                // Verificar si la ciudad o el reencaminamiento no coinciden con la ciudad del usuario logueado
                if ($admision->reencaminamiento !== $userCity && $admision->ciudad !== $userCity) {
                    $admisionesInvalidas[] = $admision->codigo;
                }
            }

            if (!empty($admisionesInvalidas)) {
                // Mostrar alerta con las admisiones que no cumplen la condición
                $admisionesInvalidasStr = implode(', ', $admisionesInvalidas);
                session()->flash('error', "Los siguientes envios no son de tu ciudad para mandarlo a ventanilla: {$admisionesInvalidasStr}");
                return;
            }

            // Continuar con el envío a ventanilla
            foreach ($admisiones as $admision) {
                $admision->estado = 11; // Cambiar estado a 11 (ventanilla)
                $admision->save();

                // Registrar el evento
                Eventos::create([
                    'accion' => 'Mandar a ventanilla',
                    'descripcion' => 'La admisión fue enviada a la ventanilla.',
                    'codigo' => $admision->codigo,
                    'user_id' => Auth::id(),
                ]);
                Historico::create([
                    'numero_guia' => $admision->codigo, // Asignar el código único de admisión al número de guía
                    'fecha_actualizacion' => now(), // Usar el timestamp actual para la fecha de actualización
                    'id_estado_actualizacion' => 4, // Estado inicial: 1
                    'estado_actualizacion' => '"Operador" en posesión del envío', // Descripción del estado
                ]);
            }

            // Emitir evento para recargar la página
            $this->dispatch('reload-page');

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
        if (empty($this->selectedAdmisiones) && empty($this->selectedSolicitudesExternas)) {
            session()->flash('error', 'Debe seleccionar al menos una admisión o solicitud externa.');
            return;
        }

        if (empty($this->selectedDepartment)) {
            session()->flash('error', 'Debe seleccionar un departamento para reencaminar.');
            return;
        }

        // Mapeo de ciudades a códigos abreviados
        $cityCodes = [
            'LA PAZ'       => 'LPB',
            'SANTA CRUZ'   => 'SRZ',
            'COCHABAMBA'   => 'CBB',
            'ORURO'        => 'ORU',
            'POTOSI'       => 'PTI',
            'TARIJA'       => 'TJA',
            'CHUQUISACA'   => 'SRE',
            'BENI'         => 'TDD', // Trinidad
            'PANDO'        => 'CIJ', // Cobija
        ];

        // Convertir la ciudad
        $reencaminamientoAbreviado = $cityCodes[$this->selectedDepartment] ?? $this->selectedDepartment; // Para solicitudes externas
        $reencaminamientoCompleto = $this->selectedDepartment; // Para admisiones internas

        // Generar manifiesto si no se ingresó manualmente
        if (empty($this->manualManifiesto)) {
            $this->manualManifiesto = $this->generarManifiesto(Auth::user()->city);
        }

        $errores = [];

        // ✅ Procesar solicitudes externas (USAR CÓDIGO ABREVIADO)
        if (!empty($this->selectedSolicitudesExternas)) {
            foreach ($this->selectedSolicitudesExternas as $guia) {
                $response = Http::put("http://172.65.10.52:8450/api/solicitudes/reencaminar", [
                    'guia' => $guia,
                    'reencaminamiento' => $reencaminamientoAbreviado, // Se envía la ABREVIACIÓN (CBB, ORU, etc.)
                    'manifiesto' => $this->manualManifiesto,
                ]);

                if (!$response->successful()) {
                    $errores[] = "Error en la solicitud externa {$guia}: " . $response->body();
                }
            }
        }

        // ✅ Procesar admisiones internas (USAR NOMBRE COMPLETO)
        if (!empty($this->selectedAdmisiones)) {
            $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            foreach ($admisiones as $admision) {
                $admision->estado           = 6; // Mandado a regional
                $admision->manifiesto       = $this->manualManifiesto;
                $admision->reencaminamiento = $reencaminamientoCompleto; // Se almacena el NOMBRE COMPLETO
                $admision->tipo_transporte  = $this->selectedTransport;
                $admision->numero_vuelo     = $this->numeroVuelo;
                $admision->save();

                // Registrar eventos
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

        // Generar el PDF con las admisiones seleccionadas
        $solicitudesExternasSeleccionadas = collect($this->solicitudesExternas)
            ->whereIn('guia', $this->selectedSolicitudesExternas);

        $this->selectedAdmisionesList = $admisiones ?? collect();
        return $this->generarPdf($solicitudesExternasSeleccionadas);

        // Limpiar selección
        $this->selectedAdmisiones        = [];
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
        // Petición al primer sistema que está en carteros.php
        $response = Http::get('http://172.65.10.52:8450/api/ems/estado/5');

        if ($response->successful()) {
            $this->solicitudesExternas = $response->json();
        } else {
            $this->solicitudesExternas = [];
        }


        // Resto de lo que ya tenías en mount()
        $this->origen = Auth::user()->city;
        $this->ciudad = "";
        $this->cantidad = 1;
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
        $this->dispatch('reloadPage');

        // Descargar automáticamente el PDF
        return response()->streamDownload(
            fn() => print($pdf->stream('admision.pdf')),
            'admision.pdf'
        );

        session()->flash('message', 'Admisión creada exitosamente.');
    }




    public function reimprimirManifiesto()
    {
        // Validar que el manifiesto haya sido ingresado o generar uno
        if (empty($this->manifiestoInput)) {
            // Si no hay, generamos uno automáticamente
            $this->manifiestoInput = $this->generarManifiesto(Auth::user()->city);
        }

        // Buscar admisiones internas con ese manifiesto
        $admisionesExistentes = Admision::where('manifiesto', $this->manifiestoInput)->get();

        // Consultar en la API si hay registros con ese manifiesto
        $response = Http::get("http://172.65.10.52:8450/api/solicitudes/manifiesto/{$this->manifiestoInput}");

        $solicitudesExternasSeleccionadas = collect(); // Inicializar colección vacía

        if ($response->successful()) {
            $data = $response->json();
            // Ajustar si la respuesta está anidada, por ejemplo, en $data['data']
            $solicitudesExternasSeleccionadas = collect($data);
        }

        // Verificar si no hay resultados en ambos sistemas
        if ($admisionesExistentes->isEmpty() && $solicitudesExternasSeleccionadas->isEmpty()) {
            session()->flash('error', 'No se encontraron admisiones con el manifiesto ingresado en ningún sistema.');
            return;
        }

        // Asignar ambos para que coincidan
        $this->manualManifiesto = $this->manifiestoInput;
        $this->currentManifiesto = $this->manifiestoInput;

        // (Opcional) Si quieres reimprimir también las admisiones locales, setéalas 
        // en la lista seleccionada para que tu generarPdf() las lea:
        $this->selectedAdmisionesList = $admisionesExistentes;

        // Generar el PDF con todas las admisiones del manifiesto
        return $this->generarPdf($solicitudesExternasSeleccionadas);
    }






    public function generarPdf($solicitudesExternasSeleccionadas)
    {
        // 1. Obtener admisiones locales seleccionadas (si las hay)
        $admisionesSeleccionadas = $this->selectedAdmisionesList;

        // 2. Convertir $solicitudesExternasSeleccionadas a colección si es un array
        if (is_array($solicitudesExternasSeleccionadas)) {
            $solicitudesExternasSeleccionadas = collect($solicitudesExternasSeleccionadas);
        }

        // 3. Verificar si hay admisiones seleccionadas o solicitudes externas
        if ($admisionesSeleccionadas->isEmpty() && $solicitudesExternasSeleccionadas->isEmpty()) {
            session()->flash('error', 'No hay admisiones válidas para generar el PDF.');
            return;
        }

        // **Cálculo de cantidad total**
        $totalCantidad = count($admisionesSeleccionadas) + count($solicitudesExternasSeleccionadas);

        // **Cálculo de peso total**
        $totalPeso = 0;

        // Sumar peso de admisiones internas
        foreach ($admisionesSeleccionadas as $admision) {
            $peso = (float) ($admision->peso_ems ?? $admision->peso ?? 0);
            $totalPeso += $peso;
        }

        // Sumar peso de solicitudes externas
        foreach ($solicitudesExternasSeleccionadas as $solicitud) {
            $pesoExterno = (float) ($solicitud['peso_o'] ?? $solicitud['peso_v'] ?? $solicitud['peso_r'] ?? 0);
            $totalPeso += $pesoExterno;
        }

        // Datos para la plantilla del PDF
        $data = [
            'admisiones'        => $admisionesSeleccionadas,
            'solicitudesExternas' => $solicitudesExternasSeleccionadas,
            'currentDate'       => now()->format('d/m/Y'),
            'currentTime'       => now()->format('H:i'),
            'currentManifiesto' => $this->manualManifiesto, // 👈 Mantenemos el manifiesto
            'loggedInUserCity'  => Auth::user()->city,
            'destinationCity'   => $this->selectedDepartment ?? ($admisionesSeleccionadas->first()->reencaminamiento ?? $admisionesSeleccionadas->first()->ciudad ?? ''),
            'selectedTransport' => $this->selectedTransport,
            'numeroVuelo'       => $this->numeroVuelo,
            'totalCantidad'     => $totalCantidad, // 👈 Cantidad total corregida
            'totalPeso'         => number_format($totalPeso, 2, '.', ''), // 👈 Peso total corregido con 2 decimales
        ];

        // Generar el PDF con DomPDF
        $pdf = Pdf::loadView('pdfs.cn33', $data)->setPaper('letter', 'portrait');

        // Descargar el PDF directamente
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
        $this->updatedSelectedAdmisiones(); // Actualizar la lista
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

        // Validar que el usuario haya ingresado o generado un manifiesto
        if (empty($this->manualManifiesto)) {
            // Si no hay manifiesto manual, generar uno automáticamente
            $this->manualManifiesto = $this->generarManifiesto(Auth::user()->city);
        }

        // Procesar admisiones internas (del sistema actual)
        if (!empty($this->selectedAdmisiones)) {
            $admisiones = Admision::whereIn('id', $this->selectedAdmisiones)->get();

            foreach ($admisiones as $admision) {
                $admision->estado = 6; // Cambiar al estado 6 (CN-33)
                $admision->manifiesto = $this->manualManifiesto;
                $admision->save();

                // Registrar evento
                Eventos::create([
                    'accion'      => 'Añadir a CN-33',
                    'descripcion' => "Se añadió al manifiesto {$this->manualManifiesto}.",
                    'codigo'      => $admision->codigo,
                    'user_id'     => Auth::id(),
                ]);
            }
        }

        // Procesar solicitudes externas (del otro sistema)
        $errores = [];

        if (!empty($this->selectedSolicitudesExternas)) {
            foreach ($this->selectedSolicitudesExternas as $guia) {
                // Actualizar en la API
                $response = Http::put("http://172.65.10.52:8450/api/solicitudes/reencaminar", [
                    'guia'       => $guia,
                    'manifiesto' => $this->manualManifiesto, // Asignar el manifiesto generado
                    'estado'     => 8, // Cambiar estado a 8
                ]);

                if (!$response->successful()) {
                    $errores[] = "Error en solicitud externa {$guia}: " . $response->body();
                }
            }
        }

        // Limpiar selección después de procesar
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
}
public function generarContrato()
{
    // ✅ Validación obligatoria
    $this->validate([
        'contratoCodigo'      => 'required|string',
        'contratoPeso'        => 'required|numeric|min:0.001',
        'contratoObservacion' => 'nullable|string|max:500',
    ], [
        'contratoCodigo.required' => 'Debe ingresar un código.',
        'contratoPeso.required'   => 'Debe ingresar un peso.',
        'contratoPeso.numeric'    => 'El peso debe ser numérico.',
        'contratoPeso.min'        => 'El peso debe ser mayor a 0.',
    ]);

    try {
        \DB::beginTransaction();

        $admision = Admision::where('codigo', trim((string)$this->contratoCodigo))->first();

        $peso = (float) $this->contratoPeso;
        $textoObs = trim((string) ($this->contratoObservacion ?? ''));

        if ($admision) {
            // --- ACTUALIZA ---
            $admision->peso          = $peso;
            $admision->peso_ems      = $peso;
            $admision->peso_regional = $peso;

            if ($textoObs !== '') {
                $admision->observacion = trim(($admision->observacion ? $admision->observacion.' | ' : '').$textoObs);
            }

            $admision->save();

            Eventos::create([
                'accion'      => 'Generar Contrato',
                'descripcion' => "Actualización por contrato: peso/peso_ems/peso_regional = {$peso}.",
                'codigo'      => $admision->codigo,
                'user_id'     => Auth::id(),
            ]);

            \DB::commit();

            $this->showContratoModal = false;
            $this->resetContratoFields();
            $this->dispatch('reload-page');
            session()->flash('message', 'Contrato actualizado: pesos y observación guardados.');
            return;
        }

        // --- CREA NUEVO ---
        $tarifaIdDefecto = 1; // ajusta si tu tarifa base es otra

        $admision = Admision::create([
            'origen'       => Auth::user()->city ?? 'LA PAZ',
            'fecha'        => now(),
            'servicio'     => 'CONTRATO',
            'cantidad'     => 1,
            'peso'         => $peso,
            'peso_ems'     => $peso,
            'peso_regional'=> $peso,
            'observacion'  => ($textoObs !== '' ? $textoObs : null),
            'codigo'       => trim((string)$this->contratoCodigo),
            'precio'       => 0,
            'ciudad'       => Auth::user()->city ?? 'LA PAZ',
            'creacionadmision' => Auth::user()->name ?? null,
            'estado'       => 3,
            'tarifa_id'    => $tarifaIdDefecto,
            'user_id'      => Auth::id(),
        ]);

        Eventos::create([
            'accion'      => 'Generar Contrato',
            'descripcion' => "Creación por contrato: peso/peso_ems/peso_regional = {$peso}.",
            'codigo'      => $admision->codigo,
            'user_id'     => Auth::id(),
        ]);

        \DB::commit();

        $this->showContratoModal = false;
        $this->resetContratoFields();
        $this->dispatch('reload-page');
        session()->flash('message', 'Contrato creado: registro nuevo con pesos guardados.');
    } catch (\Throwable $e) {
        \DB::rollBack();
        session()->flash('error', 'No se pudo guardar el contrato: '.$e->getMessage());
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
