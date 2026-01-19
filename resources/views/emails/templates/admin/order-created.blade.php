@extends('core::emails.layouts.base')

@section('header')
    <h1>Nueva Orden Recibida</h1>
@endsection

@section('content')
    <!-- Main Message -->
    <div class="mb-20">
        <p><strong>Se ha recibido una nueva orden en el sistema pendiente de pago.</strong></p>
        <p>A continuación encontrarás los detalles completos de la orden:</p>
    </div>

    <!-- Order Details -->
    @if(isset($order))
    <div class="info-box">
        <h3 style="margin-top: 0;">Detalles de la Orden</h3>
        <p><strong>Número de Orden:</strong> {{ $order->order_number ?? 'N/A' }}</p>
        <p><strong>Fecha de Creación:</strong> {{ $order->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
        <p><strong>Última Actualización:</strong> {{ $order->updated_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
        <p><strong>Estado:</strong> {{ $order->status_name ?? 'N/A' }}</p>
        @if(isset($order->payment_status))
        <p><strong>Estado de Pago:</strong> {{ $order->payment_status }}</p>
        @endif
        @if(isset($order->total))
        <p><strong>Total:</strong> {{ $order->total }}</p>
        @endif
        @if(isset($order->payment_method))
        <p><strong>Método de Pago:</strong> {{ $order->payment_method }}</p>
        @endif
    </div>
    @endif

    <!-- Customer Info (always show for admin) -->
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

    <!-- Order Items Table -->
    @if(isset($items) && count($items) > 0)
    <div class="info-box">
        <h3 style="margin-top: 0;">Productos de la Orden</h3>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
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

        @if(isset($payment['expires_at']))
        <p><strong>Expira:</strong> {{ \Carbon\Carbon::parse($payment['expires_at'])->format('d/m/Y H:i') }}</p>
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
            @if(isset($discount['type']))
            <em>({{ $discount['type'] }})</em>
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

    <!-- Order Notes (admin only) -->
    @if(isset($order->notes))
    <h3>Notas de la Orden</h3>
    <div class="info-box">
        <p>{{ $order->notes }}</p>
    </div>
    @endif

    <!-- Admin Action Button -->
    @if(isset($action_url) && isset($action_text))
    <div class="text-center mt-20">
        <a href="{{ $action_url }}" class="button">{{ $action_text }}</a>
    </div>
    @endif

@endsection

@section('footer')
    <p>Sistema de Notificaciones - Panel de Administración<br>Este es un correo automático, por favor no responder directamente. Accede al panel de administración para gestionar esta orden.</p>
@endsection
