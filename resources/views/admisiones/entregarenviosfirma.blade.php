@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Paqueteria Postal
@endsection

@section('content')
@livewire('entregarenviosfirma', ['id' => $admision->id])
@include('footer')
@endsection