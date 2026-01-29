@extends('core::emails.layouts.base')

@section('header')
    <h1>Nuevo Mensaje de Contacto Recibido</h1>
@endsection

@section('content')
    <!-- Main Message -->
    <div class="mb-20">
        <p><strong>Se ha recibido un nuevo mensaje a través del formulario de contacto.</strong></p>
        <p>A continuación encontrarás los detalles completos del mensaje:</p>
    </div>

    <!-- Contact Details -->
    @if(isset($name) || isset($email))
    <div class="info-box">
        <h3 style="margin-top: 0;">Información del Remitente</h3>
        @if(isset($name))
        <p><strong>Nombre:</strong> {{ $name }}</p>
        @endif
        @if(isset($email))
        <p><strong>Email:</strong> {{ $email }}</p>
        @endif
    </div>
    @endif

    <!-- Message Content -->
    @if(isset($contact_message))
    <div class="info-box">
        <h3 style="margin-top: 0;">Mensaje</h3>
        <p style="white-space: pre-wrap;">{{ $contact_message }}</p>
    </div>
    @endif

    <!-- Timestamp -->
    <div class="info-box">
        <h3 style="margin-top: 0;">Detalles de Recepción</h3>
        <p><strong>Fecha y Hora:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Admin Action Button -->
    @if(isset($action_url) && isset($action_text))
    <div class="text-center mt-20">
        <a href="{{ $action_url }}" class="button">{{ $action_text }}</a>
    </div>
    @endif

@endsection

@section('footer')
    <p>Sistema de Notificaciones - Panel de Administración<br>Este es un correo automático, por favor no responder directamente.</p>
@endsection
