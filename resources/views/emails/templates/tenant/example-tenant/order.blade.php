{{--
    EXAMPLE: Tenant-Specific Order Email Template

    This is an example of how a tenant can override the default order email template.
    Copy this file structure to create custom templates for specific tenants.

    Path: resources/views/emails/templates/tenant/{tenant_id}/order.blade.php
    This example: tenant/{example-tenant}/order.blade.php

    When an order event is triggered for tenant "example-tenant",
    this template will be used instead of the default templates/order.blade.php
--}}

@extends('core::emails.layouts.base')

@section('header')
    {{-- Custom branded header for this tenant --}}
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
        {{-- You could add a tenant-specific logo here --}}
        <h1 style="color: white; margin: 0; font-size: 28px;">
            {!! $slots['header'] ?? '¡Gracias por tu Compra!' !!}
        </h1>
        <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0 0;">
            Tu pedido ha sido recibido y está siendo procesado
        </p>
    </div>
@endsection

@section('content')
    {{-- Main Message Slot (customizable from database) --}}
    <div class="mb-20">
        {!! $slots['main_message'] ?? '<p>Tu orden ha sido creada exitosamente.</p>' !!}
    </div>

    {{-- Order Details Box --}}
    @if(isset($order))
    <div style="background: linear-gradient(to right, #f8f9fa, #e9ecef); border-left: 5px solid #667eea; padding: 20px; margin: 25px 0; border-radius: 8px;">
        <h3 style="margin-top: 0; color: #667eea;">📦 Detalles de tu Pedido</h3>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="padding: 8px 0; border: none;"><strong>Número de Orden:</strong></td>
                <td style="padding: 8px 0; border: none; text-align: right;">{{ $order->order_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border: none;"><strong>Fecha:</strong></td>
                <td style="padding: 8px 0; border: none; text-align: right;">{{ $order->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border: none;"><strong>Estado:</strong></td>
                <td style="padding: 8px 0; border: none; text-align: right;">
                    <span style="background: #28a745; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                        {{ $order->status ?? 'N/A' }}
                    </span>
                </td>
            </tr>
            @if(isset($order->total))
            <tr style="border-top: 2px solid #667eea;">
                <td style="padding: 12px 0 0 0; border: none;"><strong style="font-size: 18px;">Total:</strong></td>
                <td style="padding: 12px 0 0 0; border: none; text-align: right;"><strong style="font-size: 18px; color: #667eea;">${{ number_format($order->total, 2) }}</strong></td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    {{-- Order Items Table with Custom Styling --}}
    @if(isset($items) && count($items) > 0)
    <h3 style="color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px;">Productos Ordenados</h3>
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <th style="padding: 15px; text-align: left; border: none;">Producto</th>
                <th style="padding: 15px; text-align: center; border: none;">Cantidad</th>
                <th style="padding: 15px; text-align: right; border: none;">Precio</th>
                <th style="padding: 15px; text-align: right; border: none;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr style="border-bottom: 1px solid #e9ecef;">
                <td style="padding: 15px; border: none;">
                    <strong>{{ $item['name'] ?? $item['product_name'] ?? 'N/A' }}</strong>
                </td>
                <td style="padding: 15px; text-align: center; border: none;">
                    <span style="background: #f8f9fa; padding: 5px 15px; border-radius: 15px;">
                        {{ $item['quantity'] ?? 1 }}
                    </span>
                </td>
                <td style="padding: 15px; text-align: right; border: none;">${{ number_format($item['price'] ?? 0, 2) }}</td>
                <td style="padding: 15px; text-align: right; border: none;"><strong>${{ number_format(($item['quantity'] ?? 1) * ($item['price'] ?? 0), 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Customer Info (Optional) --}}
    @if(isset($customer))
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #333;">👤 Información del Cliente</h3>
        <p style="margin: 5px 0;"><strong>Nombre:</strong> {{ $customer->name ?? 'N/A' }}</p>
        <p style="margin: 5px 0;"><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</p>
        @if(isset($customer->phone))
        <p style="margin: 5px 0;"><strong>Teléfono:</strong> {{ $customer->phone }}</p>
        @endif
    </div>
    @endif

    {{-- Shipping Address (Optional) --}}
    @if(isset($shipping_address))
    <div style="background: #fff3cd; border-left: 5px solid #ffc107; padding: 20px; margin: 25px 0; border-radius: 8px;">
        <h3 style="margin-top: 0; color: #856404;">🚚 Dirección de Envío</h3>
        <p style="margin: 5px 0; color: #856404;">
            {{ $shipping_address->street ?? '' }}<br>
            {{ $shipping_address->city ?? '' }}, {{ $shipping_address->state ?? '' }} {{ $shipping_address->zip_code ?? '' }}<br>
            {{ $shipping_address->country ?? '' }}
        </p>
    </div>
    @endif

    {{-- Tenant-Specific: Special Benefits Section --}}
    <div style="background: linear-gradient(to right, #d4edda, #c3e6cb); border: 2px dashed #28a745; padding: 20px; margin: 25px 0; border-radius: 8px; text-align: center;">
        <h3 style="margin-top: 0; color: #155724;">🎁 Beneficios Exclusivos</h3>
        <p style="color: #155724; margin: 10px 0;">
            ✅ Envío gratis en compras superiores a $50<br>
            ✅ Acumulas puntos con cada compra<br>
            ✅ Garantía de satisfacción 100%<br>
            ✅ Devoluciones gratis por 30 días
        </p>
    </div>

    {{-- Action Button (optional) --}}
    @if(isset($action_url) && isset($action_text))
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $action_url }}" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
            {{ $action_text }}
        </a>
    </div>
    @endif

    {{-- Additional Info Slot (customizable from database) --}}
    @if(isset($slots['additional_info']))
    <div class="mt-20">
        {!! $slots['additional_info'] !!}
    </div>
    @endif

    {{-- Tenant-Specific: Help Section --}}
    <div style="background: #e7f3ff; border-left: 5px solid #007bff; padding: 20px; margin: 25px 0; border-radius: 8px;">
        <h3 style="margin-top: 0; color: #004085;">💬 ¿Necesitas Ayuda?</h3>
        <p style="color: #004085; margin: 5px 0;">
            Nuestro equipo de soporte está disponible 24/7 para asistirte:
        </p>
        <ul style="color: #004085; margin: 10px 0;">
            <li>Email: support@example-tenant.com</li>
            <li>WhatsApp: +1 (555) 123-4567</li>
            <li>Chat en vivo: www.example-tenant.com/chat</li>
        </ul>
    </div>
@endsection

@section('footer')
    {{-- Custom footer for this tenant --}}
    <div style="text-align: center;">
        {!! $slots['footer'] ?? '<p>Gracias por tu compra.</p>' !!}

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p style="margin: 5px 0; font-size: 14px; color: #6b7280;">
                <strong>Example Tenant Inc.</strong><br>
                123 Commerce Street, Suite 100<br>
                Business City, BC 12345<br>
                www.example-tenant.com
            </p>

            <div style="margin: 15px 0;">
                <a href="#" style="color: #667eea; text-decoration: none; margin: 0 10px;">Facebook</a>
                <a href="#" style="color: #667eea; text-decoration: none; margin: 0 10px;">Instagram</a>
                <a href="#" style="color: #667eea; text-decoration: none; margin: 0 10px;">Twitter</a>
            </div>

            <p style="margin: 10px 0; font-size: 12px; color: #9ca3af;">
                Este es un correo automático. Por favor no respondas a este mensaje.
            </p>
        </div>
    </div>
@endsection
