@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('iniciaradmin')
@endhasrole
@hasrole ('ADMISION')
@livewire('iniciar')
@endhasrole
@include('footer')
@endsection
