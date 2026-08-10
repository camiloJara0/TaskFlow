@extends('emails.layouts.app')

@section('contenido')
    <h2 class="saludo">Hola, {{ $destinatario->nombre }}</h2>
    <p class="texto"><b>{{ $datos['autor'] ?? 'Alguien' }}</b> comentó en la tarea
        <b>{{ $datos['tarea']['titulo'] ?? '' }}</b>:</p>

    <div class="detalle">
        <p class="detalle-sub">{{ $datos['comentario'] ?? '' }}</p>
    </div>

    @if (!empty($datos['url']))
        <div class="centro">
            <a class="btn" href="{{ $datos['url'] }}">Ver comentario</a>
        </div>
    @endif
@endsection
