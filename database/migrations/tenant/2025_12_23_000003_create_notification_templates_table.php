<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // User-friendly name: "Plantilla de Orden Creada"
            $table->string('template_key')->unique(); // Identifies base template: "order.created"
            $table->string('subject'); // Subject with variables: "Tu orden #{{order_number}}"
            $table->json('slots')->nullable(); // Editable content slots
            $table->json('available_variables')->nullable(); // Variables available for this template
            $table->boolean('is_system')->default(false); // System templates can't be deleted
            $table->timestamps();
        });

        // Insert default templates
        $this->insertDefaultTemplates();
    }

    /**
     * Insert default notification templates
     */
    protected function insertDefaultTemplates(): void
    {
        $templates = [
            // Order Templates
            [
                'name' => 'Orden Creada',
                'template_key' => 'order.created',
                'subject' => 'Tu orden #{{order.order_number}} ha sido creada',
                'slots' => json_encode([
                    'header' => '¡Gracias por tu Orden!',
                    'main_message' => '<p><strong>Hemos recibido tu orden exitosamente.</strong></p><p>Estamos esperando la confirmación de tu pago para comenzar a procesar tu pedido.</p>',
                    'footer' => '<p>Gracias por confiar en nosotros.<br>Te notificaremos cuando recibamos tu pago.<br>Este es un correo automático, por favor no responder.</p>',
                ]),
                'available_variables' => json_encode([
                    'order.order_number', 'order.total', 'order.status', 'order.created_at',
                    'customer.name', 'customer.email', 'customer.phone',
                ]),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Orden Enviada',
                'template_key' => 'order.shipped',
                'subject' => '¡Tu orden #{{order.order_number}} ha sido enviada!',
                'slots' => json_encode([
                    'header' => '¡Tu Pedido Está en Camino!',
                    'main_message' => '<p>Hola <strong>{{customer.name}}</strong>,</p><p>Nos complace informarte que tu orden ha sido enviada y está en camino a tu dirección.</p>',
                    'additional_info' => '<div class="info-box"><p><strong>Número de Seguimiento:</strong> {{tracking_number}}</p></div>',
                    'footer' => '<p>Gracias por tu compra.<br>Puedes rastrear tu pedido usando el número de seguimiento proporcionado.</p>',
                ]),
                'available_variables' => json_encode([
                    'order.order_number', 'order.total', 'tracking_number',
                    'customer.name', 'customer.email',
                    'shipping_address.street', 'shipping_address.city', 'shipping_address.state',
                ]),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Orden Entregada',
                'template_key' => 'order.delivered',
                'subject' => 'Tu orden #{{order.order_number}} ha sido entregada',
                'slots' => json_encode([
                    'header' => '¡Orden Entregada!',
                    'main_message' => '<p>Hola <strong>{{customer.name}}</strong>,</p><p>Tu orden ha sido entregada exitosamente. Esperamos que disfrutes tu compra.</p>',
                    'footer' => '<p>Gracias por tu compra.<br>¡Esperamos verte pronto!</p>',
                ]),
                'available_variables' => json_encode(['order.order_number', 'customer.name']),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // User Templates
            [
                'name' => 'Bienvenida',
                'template_key' => 'user.registered',
                'subject' => 'Bienvenido a {{app_name}}, {{user.name}}',
                'slots' => json_encode([
                    'header' => '¡Bienvenido!',
                    'main_message' => '<p>Hola <strong>{{user.name}}</strong>,</p><p>¡Gracias por registrarte! Estamos emocionados de tenerte con nosotros.</p>',
                    'footer' => '<p>Si tienes alguna pregunta, nuestro equipo de soporte está aquí para ayudarte.</p>',
                ]),
                'available_variables' => json_encode(['user.name', 'user.email', 'user.username', 'app_name']),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Verificación de Email',
                'template_key' => 'user.email_verification',
                'subject' => 'Verifica tu correo electrónico',
                'slots' => json_encode([
                    'header' => 'Verificación de Correo',
                    'main_message' => '<p>Hola <strong>{{user.name}}</strong>,</p><p>Por favor verifica tu correo electrónico haciendo clic en el botón de abajo.</p>',
                    'footer' => '<p>Si no creaste esta cuenta, puedes ignorar este correo.</p>',
                ]),
                'available_variables' => json_encode(['user.name', 'user.email', 'action_url', 'expiry_time']),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Restablecimiento de Contraseña',
                'template_key' => 'user.password_reset',
                'subject' => 'Restablece tu contraseña',
                'slots' => json_encode([
                    'header' => 'Restablecer Contraseña',
                    'main_message' => '<p>Hola <strong>{{user.name}}</strong>,</p><p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el botón de abajo para continuar.</p>',
                    'footer' => '<p>Si no solicitaste este cambio, ignora este correo.<br>Tu contraseña permanecerá sin cambios.</p>',
                ]),
                'available_variables' => json_encode(['user.name', 'user.email', 'action_url', 'expiry_time']),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Payment Templates
            [
                'name' => 'Pago Aprobado',
                'template_key' => 'payment.approved',
                'subject' => 'Pago Aprobado - Transacción #{{payment.transaction_id}}',
                'slots' => json_encode([
                    'header' => '¡Pago Aprobado!',
                    'main_message' => '<p>Tu pago de <strong>${{payment.amount}}</strong> ha sido procesado exitosamente.</p>',
                    'footer' => '<p>Gracias por tu pago.<br>Recibirás tu compra pronto.</p>',
                ]),
                'available_variables' => json_encode([
                    'payment.transaction_id', 'payment.amount', 'payment.payment_method',
                    'payment.status', 'order.order_number',
                ]),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pago Fallido',
                'template_key' => 'payment.failed',
                'subject' => 'Pago No Procesado - Transacción #{{payment.transaction_id}}',
                'slots' => json_encode([
                    'header' => 'Pago No Procesado',
                    'main_message' => '<p>Lo sentimos, tu pago de <strong>${{payment.amount}}</strong> no pudo ser procesado.</p><p>Por favor, verifica tu método de pago e intenta nuevamente.</p>',
                    'footer' => '<p>Si el problema persiste, contacta a tu banco o a nuestro equipo de soporte.</p>',
                ]),
                'available_variables' => json_encode([
                    'payment.transaction_id', 'payment.amount', 'payment.payment_method',
                    'payment.status', 'error_message',
                ]),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('notification_templates')->insert($templates);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
