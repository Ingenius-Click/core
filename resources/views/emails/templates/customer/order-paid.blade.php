@extends('core::emails.layouts.base')

@section('header')
    <h1>{!! $slots['header'] ?? '¡Pago Confirmado!' !!}</h1>
@endsection

@section('content')
    <!-- Main Message Slot -->
    <div class="mb-20">
        {!! $slots['main_message'] ?? '<p><strong>¡Tu pago ha sido confirmado exitosamente!</strong></p><p>Hemos generado tu factura y estamos preparando tu pedido para el envío.</p>' !!}
    </div>

    <!-- Invoice Details -->
    @if(isset($invoice))
    <div class="info-box">
        <h3 style="margin-top: 0;">Detalles de la Factura</h3>
        <p><strong>Número de Factura:</strong> {{ $invoice->invoice_number ?? 'N/A' }}</p>
        <p><strong>Fecha de Pago:</strong> {{ $invoice->payment_date_formatted ?? 'N/A' }}</p>
        @if(isset($invoice->total))
        <p><strong>Total:</strong> {{ $invoice->total }}</p>
        @endif
    </div>
    @endif

    <!-- Order Details -->
    @if(isset($order))
    <div class="info-box">
        <h3 style="margin-top: 0;">Detalles de la Orden</h3>
        <p><strong>Número de Orden:</strong> {{ $order->order_number ?? 'N/A' }}</p>
    </div>
    @endif

    <!-- Order Items Table -->
    @if(isset($items) && count($items) > 0)
    <div class="info-box">
        <h3 style="margin-top: 0;">Productos</h3>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item['name'] ?? $item['product_name'] ?? 'N/A' }}</td>
                    <td>{{ $item['quantity'] ?? 1 }}</td>
                    <td>{{ $item['price'] }}</td>
                    <td>{{ $item['total'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Customer Info -->
    @if(isset($customer))
    <div class="info-box">
        <h3 style="margin-top: 0;">Información del Cliente</h3>
        <p><strong>Nombre:</strong> {{ $customer->name ?? 'N/A' }}</p>
        <p><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</p>
        @if(isset($customer->phone))
        <p><strong>Teléfono:</strong> {{ $customer->phone }}</p>
        @endif
        @if(isset($customer->address))
        <p><strong>Dirección:</strong> {{ $customer->address }}</p>
        @endif
    </div>
    @endif

    <!-- Shipment Information (from extension) -->
    @if(isset($shipment))
    <div class="info-box">
        <h3 style="margin-top: 0;">Información de Envío</h3>

        @if(isset($shipment['tracking_number']))
        <p><strong>Número de Rastreo:</strong> {{ $shipment['tracking_number'] }}</p>
        @endif

        @if(isset($shipment['price_formatted']))
        <p><strong>Costo de Envío:</strong> {{ $shipment['price_formatted'] }}</p>
        @endif

        @if(isset($shipment['beneficiary_name']))
        <p><strong>Destinatario:</strong> {{ $shipment['beneficiary_name'] }}</p>
        @endif

        @if(isset($shipment['beneficiary_email']))
        <p><strong>Email del Destinatario:</strong> {{ $shipment['beneficiary_email'] }}</p>
        @endif

        @if(isset($shipment['beneficiary_phone']))
        <p><strong>Teléfono del Destinatario:</strong> {{ $shipment['beneficiary_phone'] }}</p>
        @endif

        @if(isset($shipment['beneficiary_address']))
        <p><strong>Dirección de Entrega:</strong><br>
            {{ $shipment['beneficiary_address'] }}
            @if(isset($shipment['beneficiary_city']) || isset($shipment['beneficiary_state']))
            <br>{{ $shipment['beneficiary_city'] ?? '' }}@if(isset($shipment['beneficiary_city']) && isset($shipment['beneficiary_state'])), @endif{{ $shipment['beneficiary_state'] ?? '' }}
            @endif
            @if(isset($shipment['beneficiary_zip']))
            <br>CP: {{ $shipment['beneficiary_zip'] }}
            @endif
            @if(isset($shipment['beneficiary_country']))
            <br>{{ $shipment['beneficiary_country'] }}
            @endif
        </p>
        @endif

        @if(isset($shipment['pickup_address']))
        <p><strong>Dirección de Recogida:</strong> {{ $shipment['pickup_address'] }}</p>
        @endif
    </div>
    @endif

    <!-- Payment Information (from extension) -->
    @if(isset($payment))
    <div class="info-box">
        <h3 style="margin-top: 0;">Información de Pago</h3>

        @if(isset($payment['payform_name']))
        <p><strong>Método de Pago:</strong> {{ $payment['payform_name'] }}</p>
        @endif

        @if(isset($payment['status']))
        <p><strong>Estado del Pago:</strong> {{ ucfirst($payment['status']) }}</p>
        @endif

        @if(isset($payment['reference']))
        <p><strong>Referencia:</strong> {{ $payment['reference'] }}</p>
        @endif

        @if(isset($payment['external_id']))
        <p><strong>ID Externo:</strong> {{ $payment['external_id'] }}</p>
        @endif
    </div>
    @endif

    <!-- Discounts Applied (from extension) -->
    @if(isset($discounts['items']) && is_array($discounts['items']) && count($discounts['items']) > 0)
    <div class="info-box">
        <h3 style="margin-top: 0;">Descuentos Aplicados</h3>
        @foreach($discounts['items'] as $discount)
        <p>
            <strong>{{ $discount['name'] ?? 'Descuento' }}:</strong>
            @if(isset($order->currency) && isset($discount['amount_saved_converted']))
                {{ $order->currency }} {{ number_format($discount['amount_saved_converted'] / 100, 2) }}
            @else
                ${{ number_format($discount['amount_saved'] / 100, 2) }}
            @endif
        </p>
        @endforeach

        @if(isset($discounts['total_amount_converted']) && $discounts['total_amount_converted'] > 0)
        <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ddd;">
        <p>
            <strong>Total Ahorrado:</strong>
            @if(isset($order->currency))
                {{ $order->currency }} {{ number_format($discounts['total_amount_converted'] / 100, 2) }}
            @else
                ${{ number_format($discounts['total_amount'] / 100, 2) }}
            @endif
        </p>
        @endif
    </div>
    @endif

    <!-- Action Button (optional) -->
    @if(isset($action_url) && isset($action_text))
    <div class="text-center mt-20">
        <a href="{{ $action_url }}" class="button">{{ $action_text }}</a>
    </div>
    @endif

    <!-- Additional Message Slot -->
    @if(isset($slots['additional_info']))
    <div class="mt-20">
        {!! $slots['additional_info'] !!}
    </div>
    @endif
@endsection

@section('footer')
    {!! $slots['footer'] ?? '<p>Gracias por tu compra.<br>Comenzaremos a preparar tu pedido de inmediato.<br>Este es un correo automático, por favor no responder.</p>' !!}
@endsection
