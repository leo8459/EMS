@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('emsinventarioadmin')
@endhasrole
@hasrole ('EMS')
@livewire('emsinventario')
@endhasrole@include('footer')
@endsection
