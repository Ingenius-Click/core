# Tenant-Specific Email Templates

This directory allows you to create custom email templates for specific tenants, overriding the default package templates.

## Directory Structure

```
tenant/
├── README.md
├── .gitkeep
├── tenant-1/              # Example tenant
│   ├── order.blade.php
│   └── user.blade.php
└── tenant-2/              # Another tenant
    └── payment.blade.php
```

## How the System Works

### Resolution Order

When the `NotificationTemplateRenderer` renders an email:

1. **Check for tenant-specific template** (if tenancy is initialized)
   - Path: `resources/views/emails/templates/tenant/{tenant_id}/{event_type}.blade.php`
   - View name: `core::emails.templates.tenant.{tenant_id}.{event_type}`

2. **Fall back to default template** (if tenant template doesn't exist)
   - Path: `resources/views/emails/templates/{event_type}.blade.php`
   - View name: `core::emails.templates.{event_type}`

### Available Event Types

- `order` - For order-related events (created, shipped, delivered)
- `user` - For user-related events (registration, verification, password reset)
- `payment` - For payment-related events (approved, failed)
- `general` - For any other event type

## Creating a Custom Template for a Tenant

### Step 1: Identify the Tenant ID

Find the tenant's key/ID from the `tenants` table or your tenant management system.

Example: `acme-corp`, `tenant-123`, etc.

### Step 2: Create the Tenant Directory

```bash
mkdir -p resources/views/emails/templates/tenant/acme-corp
```

### Step 3: Copy a Default Template as Starting Point

```bash
cp resources/views/emails/templates/order.blade.php \
   resources/views/emails/templates/tenant/acme-corp/order.blade.php
```

### Step 4: Customize the Template

Edit the tenant-specific template:

```blade
{{-- tenant/acme-corp/order.blade.php --}}

@extends('core::emails.layouts.base')

@section('header')
    {{-- Custom header for ACME Corp --}}
    <h1>ACME Corp - {!! $slots['header'] ?? 'Order Update' !!}</h1>
    <p>Your trusted partner since 1995</p>
@endsection

@section('content')
    {{-- Custom branding or content --}}
    <div style="background: #ff6600; padding: 10px; color: white;">
        <strong>ACME Corp Exclusive Service</strong>
    </div>

    {{-- Rest of the order template --}}
    {!! $slots['main_message'] ?? '' !!}

    {{-- ... rest of template logic ... --}}
@endsection
```

### Step 5: Test

The system will automatically use the tenant-specific template when:
- Tenancy is initialized
- An order event is triggered for `acme-corp` tenant
- The custom template file exists

## Integration with NotificationTemplate System

### You Still Get All the Benefits

Even with custom Blade templates, you still have:

✅ **Variable replacement**: `{{order.order_number}}`, `{{customer.name}}`
✅ **Slots customization**: Via the `notification_templates` table
✅ **Database-driven content**: Admins can still customize slots in the backoffice
✅ **Template validation**: Available variables are still validated

### How Slots Work with Custom Templates

The `$slots` array is passed to your custom template:

```blade
{{-- Your custom tenant template can use slots --}}
<div class="custom-header">
    {!! $slots['header'] ?? 'Default Header' !!}
</div>

<div class="custom-body">
    {!! $slots['main_message'] ?? 'Default message' !!}
</div>

{{-- You can even add NEW slots specific to this tenant --}}
@if(isset($slots['acme_special_offer']))
    <div class="special-offer">
        {!! $slots['acme_special_offer'] !!}
    </div>
@endif
```

## Use Cases

### Use Case 1: Complete Brand Override

Create completely different layouts for different tenants:
- Different colors, logos, fonts
- Different email structure
- Custom sections unique to that tenant

### Use Case 2: Minor Tweaks

Just override specific templates for specific tenants:
- Custom order confirmation for enterprise clients
- Special payment messaging for certain regions
- Additional legal disclaimers for regulated industries

### Use Case 3: White-Label Solutions

Each tenant gets their own branded emails:
- Custom logos and colors
- Tenant-specific contact information
- Unique footer content

## Best Practices

### 1. Start with Defaults

Always copy from the default template and modify, rather than starting from scratch. This ensures:
- You don't miss important variables
- The structure remains compatible
- Future updates to defaults can be merged easier

### 2. Document Customizations

Add comments in your custom templates explaining what was changed and why:

```blade
{{-- ACME Corp customization: Added special warranty section --}}
@if(isset($order))
    <div class="warranty-notice">
        All ACME orders include lifetime warranty
    </div>
@endif
```

### 3. Test Thoroughly

Test the custom template with:
- All relevant event types
- Different data scenarios
- Both customer and admin recipient views

### 4. Version Control

Consider:
- Committing tenant templates to version control for important clients
- Or excluding them (add to .gitignore) if they contain sensitive branding
- Document which tenants have custom templates

## Troubleshooting

### Template Not Being Used

**Problem:** Created a custom template but the default is still used.

**Check:**
1. Directory name matches tenant ID exactly: `tenant()->getTenantKey()`
2. File name matches event type: `order.blade.php`, `user.blade.php`, etc.
3. File is valid Blade syntax (test compilation)
4. Tenancy is properly initialized when email is sent

**Debug:**
```php
// In your code, check:
if (tenancy()->initialized) {
    $tenantId = tenancy()->tenant->getTenantKey();
    $expectedPath = "core::emails.templates.tenant.{$tenantId}.order";
    dump(view()->exists($expectedPath)); // Should be true
}
```

### Syntax Errors

**Problem:** Email fails to render with tenant template.

**Solution:**
- Check Blade syntax
- Ensure all `@` directives are closed
- Verify variable names match what's passed from renderer

### Slots Not Working

**Problem:** Slots show as empty or default values.

**Solution:**
- Slots are populated from `notification_templates` table
- Check that template configuration has proper slots defined
- Verify slot names match between database and Blade template

## Example: Complete Custom Template

```blade
{{-- resources/views/emails/templates/tenant/acme-corp/order.blade.php --}}

@extends('core::emails.layouts.base')

@section('header')
    <div style="background: #ff6600; padding: 20px;">
        <img src="https://acme-corp.com/logo.png" alt="ACME Corp" style="max-width: 200px;">
        <h1 style="color: white; margin: 10px 0 0 0;">
            {!! $slots['header'] ?? 'Order Confirmation' !!}
        </h1>
    </div>
@endsection

@section('content')
    {!! $slots['main_message'] ?? '' !!}

    @if(isset($order))
    <div style="background: #f0f0f0; padding: 15px; border-left: 4px solid #ff6600;">
        <h3 style="margin-top: 0;">Order Details</h3>
        <p><strong>Order #:</strong> {{ $order->order_number ?? 'N/A' }}</p>
        <p><strong>Total:</strong> ${{ number_format($order->total ?? 0, 2) }}</p>

        {{-- ACME-specific: Show estimated delivery --}}
        <p><strong>Estimated Delivery:</strong> 2-3 business days</p>
        <p><strong>Tracking:</strong> Available within 24 hours</p>
    </div>
    @endif

    @if(isset($items) && count($items) > 0)
    <h3>Items Ordered</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: #ff6600; color: white;">
            <tr>
                <th style="padding: 10px; text-align: left;">Product</th>
                <th style="padding: 10px;">Qty</th>
                <th style="padding: 10px;">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;">{{ $item['name'] ?? 'N/A' }}</td>
                <td style="padding: 10px; text-align: center;">{{ $item['quantity'] ?? 1 }}</td>
                <td style="padding: 10px; text-align: right;">${{ number_format($item['price'] ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ACME-specific: Loyalty points --}}
    <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107;">
        <strong>🎁 You earned 50 ACME Rewards points with this purchase!</strong>
    </div>

    {!! $slots['additional_info'] ?? '' !!}
@endsection

@section('footer')
    {!! $slots['footer'] ?? '' !!}

    {{-- ACME-specific footer --}}
    <div style="margin-top: 20px; font-size: 12px; color: #666;">
        <p>ACME Corporation | support@acme-corp.com | 1-800-ACME-NOW</p>
        <p>123 Innovation Drive, Tech City, TC 12345</p>
    </div>
@endsection
```

## Summary

The tenant-specific template system gives you the **best of both worlds**:

✅ **Database-driven flexibility** - Admins can still customize content via slots
✅ **Full design control** - Developers can override entire templates per tenant
✅ **Automatic fallback** - No tenant template? Default is used seamlessly
✅ **Zero configuration** - Just create the file, it works automatically

For most tenants, the default templates + slot customization will be sufficient. For premium clients or special cases, you can create fully custom templates.
