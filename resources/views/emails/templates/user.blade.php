@extends('core::emails.layouts.base')

@section('header')
    <h1>{!! $slots['header'] ?? 'Notificación de Cuenta' !!}</h1>
@endsection

@section('content')
    <!-- Main Message Slot -->
    <div class="mb-20">
        {!! $slots['main_message'] ?? '' !!}
    </div>

    <!-- User Info -->
    @if(isset($user))
    <div class="info-box">
        <h3 style="margin-top: 0;">Información de Usuario</h3>
        <p><strong>Nombre:</strong> {{ $user->name ?? 'N/A' }}</p>
        <p><strong>Email:</strong> {{ $user->email ?? 'N/A' }}</p>
        @if(isset($user->username))
        <p><strong>Usuario:</strong> {{ $user->username }}</p>
        @endif
        @if(isset($user->created_at))
        <p><strong>Miembro desde:</strong> {{ $user->created_at->format('d/m/Y') }}</p>
        @endif
    </div>
    @endif

    <!-- Verification/Action Button -->
    @if(isset($action_url) && isset($action_text))
    <div class="text-center mt-20">
        <a href="{{ $action_url }}" class="button">{{ $action_text }}</a>
    </div>
    @endif

    <!-- Security Warning (for password reset, etc.) -->
    @if(isset($is_security_action) && $is_security_action)
    <div class="warning-box mt-20">
        <p><strong>⚠️ Importante:</strong></p>
        <p>Si no solicitaste esta acción, ignora este correo o contacta a soporte inmediatamente.</p>
        @if(isset($expiry_time))
        <p class="text-muted">Este enlace expira en {{ $expiry_time }}.</p>
        @endif
    </div>
    @endif

    <!-- Additional Info Slot -->
    @if(isset($slots['additional_info']))
    <div class="mt-20">
        {!! $slots['additional_info'] !!}
    </div>
    @endif
@endsection

@section('footer')
    {!! $slots['footer'] ?? '<p>Gracias por ser parte de nuestra comunidad.<br>Este es un correo automático, por favor no responder.</p>' !!}
@endsection
