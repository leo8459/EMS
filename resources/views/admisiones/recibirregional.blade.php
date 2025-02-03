@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@hasrole ('ADMINISTRADOR')
@livewire('recibirregionaladmin')
@endhasrole
@hasrole ('EMS')
@livewire('recibirregional')
@endhasrole

@include('footer')
@endsection
