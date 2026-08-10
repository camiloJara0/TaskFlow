@extends('emails.layouts.app')

@section('contenido')
    <h2 class="saludo">Hola, {{ $destinatario->nombre }}</h2>
    <p class="texto">La tarea <b>{{ $datos['tarea']['titulo'] ?? '' }}</b> cambió de estado:</p>

    <div class="estados">
        <span class="estado-pill">{{ $datos['estado_anterior'] ?? 'Sin estado' }}</span>
        <span class="estado-flecha">→</span>
        <span class="estado-pill" style="border-color: #22C55E; color: #22C55E;">{{ $datos['estado_nuevo'] ?? '' }}</span>
    </div>

    <p class="texto" style="text-align:center;">Actualizado por <b>{{ $datos['actor'] ?? 'Alguien' }}</b></p>

    @if (!empty($datos['url']))
        <div class="centro">
            <a class="btn" href="{{ $datos['url'] }}">Ver tarea</a>
        </div>
    @endif
@endsection
