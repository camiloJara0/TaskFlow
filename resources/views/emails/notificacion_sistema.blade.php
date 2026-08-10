@extends('emails.layouts.app')

@section('contenido')
    <h2 class="saludo">Hola, {{ $destinatario->nombre }}</h2>
    <p class="texto">{{ $datos['mensaje'] ?? '' }}</p>

    @if (!empty($datos['detalle_titulo']))
        <div class="detalle">
            <p class="detalle-titulo">{{ $datos['detalle_titulo'] }}</p>
            @if (!empty($datos['detalle_sub']))
                <p class="detalle-sub">{!! $datos['detalle_sub'] !!}</p>
            @endif
        </div>
    @endif

    @if (!empty($datos['url']))
        <div class="centro">
            <a class="btn" href="{{ $datos['url'] }}">{{ $datos['boton_texto'] ?? 'Ver más' }}</a>
        </div>
    @endif
@endsection
