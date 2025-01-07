@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('admisionesgeneradasadmin')
@endhasrole
@hasrole ('ADMISION')
@livewire('admisionesgeneradas')
@endhasrole
@include('footer')
@endsection
