@extends('core::emails.layouts.base')

@section('header')
    <h1>{!! $slots['header'] ?? 'Notificación de Pago' !!}</h1>
@endsection

@section('content')
    <!-- Main Message Slot -->
    <div class="mb-20">
        {!! $slots['main_message'] ?? '' !!}
    </div>

    <!-- Payment Details -->
    @if(isset($payment))
    <div class="info-box">
        <h3 style="margin-top: 0;">Detalles del Pago</h3>
        <p><strong>ID de Transacción:</strong> {{ $payment->transaction_id ?? 'N/A' }}</p>
        <p><strong>Monto:</strong> ${{ number_format($payment->amount ?? 0, 2) }}</p>
        <p><strong>Método de Pago:</strong> {{ $payment->payment_method ?? 'N/A' }}</p>
        <p><strong>Estado:</strong> {{ $payment->status ?? 'N/A' }}</p>
        <p><strong>Fecha:</strong> {{ $payment->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
    </div>
    @endif

    <!-- Related Order -->
    @if(isset($order))
    <h3>Orden Relacionada</h3>
    <p><strong>Número de Orden:</strong> {{ $order->order_number ?? 'N/A' }}</p>
    @if(isset($order->total))
    <p><strong>Total de Orden:</strong> ${{ number_format($order->total, 2) }}</p>
    @endif
    @endif

    <!-- Payment Breakdown -->
    @if(isset($breakdown) && count($breakdown) > 0)
    <h3>Desglose</h3>
    <table>
        <tbody>
            @foreach($breakdown as $item)
            <tr>
                <td>{{ $item['label'] ?? '' }}</td>
                <td style="text-align: right;">${{ number_format($item['amount'] ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Action Button -->
    @if(isset($action_url) && isset($action_text))
    <div class="text-center mt-20">
        <a href="{{ $action_url }}" class="button">{{ $action_text }}</a>
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
    {!! $slots['footer'] ?? '<p>Gracias por tu pago.<br>Este es un correo automático, por favor no responder.</p>' !!}
@endsection
