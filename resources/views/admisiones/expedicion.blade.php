@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('expedicionadmin')
@endhasrole
@hasrole ('EMS')
@livewire('expedicion')
@endhasrole
@include('footer')
@endsection
