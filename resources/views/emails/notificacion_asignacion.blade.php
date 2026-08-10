@extends('emails.layouts.app')

@section('contenido')
    <h2 class="saludo">Hola, {{ $destinatario->nombre }}</h2>
    <p class="texto"><b>{{ $datos['asignador'] ?? 'Alguien' }}</b> te ha asignado una tarea:</p>

    <div class="detalle">
        <p class="detalle-titulo">{{ $datos['tarea']['titulo'] ?? 'Tarea' }}</p>
        @if (!empty($datos['tarea']['descripcion']))
            <p class="detalle-sub">{{ $datos['tarea']['descripcion'] }}</p>
        @endif
        @if (!empty($datos['tarea']['prioridad']))
            <p class="detalle-sub" style="margin-top:10px;">Prioridad:
                <span class="badge badge-claro">{{ $datos['tarea']['prioridad'] }}</span>
            </p>
        @endif
        @if (!empty($datos['tarea']['fecha_vencimiento']))
            <p class="detalle-sub" style="margin-top:6px;">Vence: <b>{{ $datos['tarea']['fecha_vencimiento'] }}</b></p>
        @endif
    </div>

    @if (!empty($datos['url']))
        <div class="centro">
            <a class="btn" href="{{ $datos['url'] }}">Ver tarea</a>
        </div>
    @endif
@endsection
