@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('entregarcarteroadmin')
@endhasrole
@hasrole ('CARTERO')
@livewire('entregarcartero')
@endhasrole
@include('footer')
@endsection
