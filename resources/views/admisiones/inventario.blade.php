@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('inventarioadmin')
@endhasrole
@hasrole ('ADMISION')
@livewire('inventario')
@endhasrole
@include('footer')
@endsection
