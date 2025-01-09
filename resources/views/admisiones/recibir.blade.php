@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('recibiradmin')
@endhasrole
@hasrole ('EMS')
@livewire('recibir')
@endhasrole
@include('footer')
@endsection
