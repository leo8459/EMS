@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('inventarioventanillaadmin')
@endhasrole
@hasrole ('VENTANILLA')
@livewire('inventarioventanilla')
@endhasrole
@hasrole ('EMS')
@livewire('inventarioventanilla')
@endhasrole
@include('footer')
@endsection
