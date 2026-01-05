@extends('core::emails.layouts.base')

@section('header')
    <h1>{!! $slots['header'] ?? 'Notificación' !!}</h1>
@endsection

@section('content')
    <!-- Main Message Slot -->
    <div class="mb-20">
        {!! $slots['main_message'] ?? '' !!}
    </div>

    <!-- Additional Info Slot -->
    @if(isset($slots['additional_info']))
    <div class="mt-20">
        {!! $slots['additional_info'] !!}
    </div>
    @endif

    <!-- Action Button (optional) -->
    @if(isset($action_url) && isset($action_text))
    <div class="text-center mt-20">
        <a href="{{ $action_url }}" class="button">{{ $action_text }}</a>
    </div>
    @endif

    <!-- Generic Data Display -->
    @if(isset($data) && is_array($data) && count($data) > 0)
    <div class="info-box mt-20">
        @foreach($data as $key => $value)
            @if(is_scalar($value))
            <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
            @endif
        @endforeach
    </div>
    @endif
@endsection

@section('footer')
    {!! $slots['footer'] ?? '<p>Este es un correo automático, por favor no responder.</p>' !!}
@endsection
