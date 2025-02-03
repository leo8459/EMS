@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')

@hasrole ('ADMINISTRADOR')
@livewire('encaminocarteroadmin')
@endhasrole
@hasrole ('CARTERO')
@livewire('encaminocartero')
@endhasrole
@include('footer')
@endsection
