@extends('emails.layouts.app')

@section('contenido')
    <h2 class="saludo">Hola, {{ $destinatario->nombre }}</h2>
    <p class="texto">Te han agregado al equipo <b>{{ $datos['equipo']['nombre'] ?? '' }}</b>.</p>

    <div class="detalle">
        <p class="detalle-titulo">{{ $datos['equipo']['nombre'] ?? 'Equipo' }}</p>
        <p class="detalle-sub">Tu rol: <span class="badge badge-claro">{{ $datos['rol'] ?? 'Miembro' }}</span></p>
    </div>

    <p class="texto">Ya puedes colaborar en sus espacios de trabajo, proyectos y tareas.</p>

    @if (!empty($datos['url']))
        <div class="centro">
            <a class="btn" href="{{ $datos['url'] }}">Ver equipo</a>
        </div>
    @endif
@endsection
