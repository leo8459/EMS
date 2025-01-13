@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('inventarioadmin')
@endhasrole
@hasrole ('VENTANILLA')
@livewire('inventarioventanilla')
@endhasrole
@include('footer')
@endsection
