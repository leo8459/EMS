@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')

@hasrole ('ADMINISTRADOR')
@livewire('encaminocarteroentregaadmin')
@endhasrole
@hasrole ('CARTERO')
@livewire('encaminocarteroentrega')
@endhasrole
@hasrole ('EMS')
@livewire('encaminocarteroentrega')
@endhasrole

@include('footer')
@endsection
