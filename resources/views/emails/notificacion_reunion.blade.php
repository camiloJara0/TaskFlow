@extends('emails.layouts.app')

@section('contenido')
    <h2 class="saludo">Hola, {{ $destinatario->nombre }}</h2>
    <p class="texto">Te han invitado a la reunión <b>{{ $datos['reunion']['titulo'] ?? '' }}</b> organizada por <b>{{ $datos['organizador'] ?? 'alguien' }}</b>.</p>

    <div class="detalle">
        <p class="detalle-titulo">{{ $datos['reunion']['titulo'] ?? 'Reunión' }}</p>
        <p class="detalle-sub">
            <b>Fecha:</b> {{ $datos['reunion']['fecha'] ?? '' }}<br>
            <b>Hora:</b> {{ $datos['reunion']['hora'] ?? '' }}<br>
            @if (!empty($datos['reunion']['descripcion']))
                <b>Descripción:</b> {{ $datos['reunion']['descripcion'] }}<br>
            @endif
            @if (!empty($datos['reunion']['url']))
                <b>Enlace:</b> <a href="{{ $datos['reunion']['url'] }}">{{ $datos['reunion']['url'] }}</a>
            @endif
        </p>
    </div>

    <p class="texto">Te enviaremos un recordatorio cuando la reunión esté por comenzar.</p>

    @if (!empty($datos['url']))
        <div class="centro">
            <a class="btn" href="{{ $datos['url'] }}">Ver reunión</a>
        </div>
    @endif
@endsection
