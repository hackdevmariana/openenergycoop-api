<?php

namespace App\Http\Requests\Api\V1\SaleOrder;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_number' => 'nullable|string|max:100|unique:sale_orders,order_number',
            'customer_id' => 'required|exists:customer_profiles,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            'customer_city' => 'nullable|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_country' => 'nullable|string|max:100',
            'customer_postal_code' => 'nullable|string|max:20',
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])
            ],
            'payment_status' => [
                'required',
                'string',
                Rule::in(['pending', 'processing', 'paid', 'failed', 'cancelled', 'refunded'])
            ],
            'payment_method' => 'nullable|string|max:100',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
            'is_urgent' => 'boolean',
            'shipping_method' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'expected_delivery_date' => 'nullable|date|after:today',
            'discount_code' => 'nullable|string|max:100',
            'organization_id' => 'nullable|exists:organizations,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.sku' => 'nullable|string|max:100',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.options' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'billing_address' => 'nullable|array',
            'billing_address.street' => 'nullable|string|max:255',
            'billing_address.city' => 'nullable|string|max:100',
            'billing_address.state' => 'nullable|string|max:100',
            'billing_address.country' => 'nullable|string|max:100',
            'billing_address.postal_code' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|array',
            'shipping_address.street' => 'nullable|string|max:255',
            'shipping_address.city' => 'nullable|string|max:100',
            'shipping_address.state' => 'nullable|string|max:100',
            'shipping_address.country' => 'nullable|string|max:100',
            'shipping_address.postal_code' => 'nullable|string|max:20',
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'string|max:500',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string|max:255',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_number.max' => 'El número de pedido no puede tener más de 100 caracteres.',
            'order_number.unique' => 'El número de pedido ya está registrado.',
            'customer_id.required' => 'El ID del cliente es obligatorio.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'customer_name.required' => 'El nombre del cliente es obligatorio.',
            'customer_name.max' => 'El nombre del cliente no puede tener más de 255 caracteres.',
            'customer_email.required' => 'El email del cliente es obligatorio.',
            'customer_email.email' => 'El email del cliente debe tener un formato válido.',
            'customer_email.max' => 'El email del cliente no puede tener más de 255 caracteres.',
            'customer_phone.max' => 'El teléfono del cliente no puede tener más de 20 caracteres.',
            'customer_address.max' => 'La dirección del cliente no puede tener más de 500 caracteres.',
            'customer_city.max' => 'La ciudad del cliente no puede tener más de 100 caracteres.',
            'customer_state.max' => 'El estado del cliente no puede tener más de 100 caracteres.',
            'customer_country.max' => 'El país del cliente no puede tener más de 100 caracteres.',
            'customer_postal_code.max' => 'El código postal del cliente no puede tener más de 20 caracteres.',
            'status.required' => 'El estado del pedido es obligatorio.',
            'status.in' => 'El estado del pedido debe ser uno de: pending, processing, shipped, delivered, cancelled, refunded.',
            'payment_status.required' => 'El estado del pago es obligatorio.',
            'payment_status.in' => 'El estado del pago debe ser uno de: pending, processing, paid, failed, cancelled, refunded.',
            'payment_method.max' => 'El método de pago no puede tener más de 100 caracteres.',
            'subtotal.numeric' => 'El subtotal debe ser un número.',
            'subtotal.min' => 'El subtotal debe ser mayor o igual a 0.',
            'tax_amount.numeric' => 'El monto de impuestos debe ser un número.',
            'tax_amount.min' => 'El monto de impuestos debe ser mayor o igual a 0.',
            'shipping_amount.numeric' => 'El monto de envío debe ser un número.',
            'shipping_amount.min' => 'El monto de envío debe ser mayor o igual a 0.',
            'discount_amount.numeric' => 'El monto de descuento debe ser un número.',
            'discount_amount.min' => 'El monto de descuento debe ser mayor o igual a 0.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un número.',
            'total.min' => 'El total debe ser mayor o igual a 0.',
            'currency.max' => 'El código de moneda no puede tener más de 3 caracteres.',
            'notes.max' => 'Las notas no pueden tener más de 2000 caracteres.',
            'internal_notes.max' => 'Las notas internas no pueden tener más de 2000 caracteres.',
            'is_urgent.boolean' => 'El estado urgente debe ser verdadero o falso.',
            'shipping_method.max' => 'El método de envío no puede tener más de 100 caracteres.',
            'tracking_number.max' => 'El número de seguimiento no puede tener más de 100 caracteres.',
            'expected_delivery_date.date' => 'La fecha de entrega esperada debe ser una fecha válida.',
            'expected_delivery_date.after' => 'La fecha de entrega esperada debe ser posterior a hoy.',
            'discount_code.max' => 'El código de descuento no puede tener más de 100 caracteres.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'items.required' => 'Los items del pedido son obligatorios.',
            'items.array' => 'Los items del pedido deben ser un array.',
            'items.min' => 'Debe haber al menos un item en el pedido.',
            'items.*.product_id.required' => 'El ID del producto es obligatorio para cada item.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.product_name.required' => 'El nombre del producto es obligatorio para cada item.',
            'items.*.product_name.max' => 'El nombre del producto no puede tener más de 255 caracteres.',
            'items.*.quantity.required' => 'La cantidad es obligatoria para cada item.',
            'items.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor o igual a 1.',
            'items.*.unit_price.required' => 'El precio unitario es obligatorio para cada item.',
            'items.*.unit_price.numeric' => 'El precio unitario debe ser un número.',
            'items.*.unit_price.min' => 'El precio unitario debe ser mayor o igual a 0.',
            'items.*.total_price.required' => 'El precio total es obligatorio para cada item.',
            'items.*.total_price.numeric' => 'El precio total debe ser un número.',
            'items.*.total_price.min' => 'El precio total debe ser mayor o igual a 0.',
            'items.*.sku.max' => 'El SKU no puede tener más de 100 caracteres.',
            'items.*.description.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'items.*.options.array' => 'Las opciones deben ser un array.',
            'tags.array' => 'Las etiquetas deben ser un array.',
            'tags.*.string' => 'Cada etiqueta debe ser una cadena de texto.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 50 caracteres.',
            'billing_address.array' => 'La dirección de facturación debe ser un array.',
            'billing_address.street.max' => 'La calle de facturación no puede tener más de 255 caracteres.',
            'billing_address.city.max' => 'La ciudad de facturación no puede tener más de 100 caracteres.',
            'billing_address.state.max' => 'El estado de facturación no puede tener más de 100 caracteres.',
            'billing_address.country.max' => 'El país de facturación no puede tener más de 100 caracteres.',
            'billing_address.postal_code.max' => 'El código postal de facturación no puede tener más de 20 caracteres.',
            'shipping_address.array' => 'La dirección de envío debe ser un array.',
            'shipping_address.street.max' => 'La calle de envío no puede tener más de 255 caracteres.',
            'shipping_address.city.max' => 'La ciudad de envío no puede tener más de 100 caracteres.',
            'shipping_address.state.max' => 'El estado de envío no puede tener más de 100 caracteres.',
            'shipping_address.country.max' => 'El país de envío no puede tener más de 100 caracteres.',
            'shipping_address.postal_code.max' => 'El código postal de envío no puede tener más de 20 caracteres.',
            'custom_fields.array' => 'Los campos personalizados deben ser un array.',
            'custom_fields.*.string' => 'Cada campo personalizado debe ser una cadena de texto.',
            'custom_fields.*.max' => 'Cada campo personalizado no puede tener más de 500 caracteres.',
            'attachments.array' => 'Los archivos adjuntos deben ser un array.',
            'attachments.*.string' => 'Cada archivo adjunto debe ser una cadena de texto.',
            'attachments.*.max' => 'Cada archivo adjunto no puede tener más de 255 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'order_number' => 'número de pedido',
            'customer_id' => 'ID del cliente',
            'customer_name' => 'nombre del cliente',
            'customer_email' => 'email del cliente',
            'customer_phone' => 'teléfono del cliente',
            'customer_address' => 'dirección del cliente',
            'customer_city' => 'ciudad del cliente',
            'customer_state' => 'estado del cliente',
            'customer_country' => 'país del cliente',
            'customer_postal_code' => 'código postal del cliente',
            'status' => 'estado del pedido',
            'payment_status' => 'estado del pago',
            'payment_method' => 'método de pago',
            'subtotal' => 'subtotal',
            'tax_amount' => 'monto de impuestos',
            'shipping_amount' => 'monto de envío',
            'discount_amount' => 'monto de descuento',
            'total' => 'total',
            'currency' => 'moneda',
            'notes' => 'notas',
            'internal_notes' => 'notas internas',
            'is_urgent' => 'estado urgente',
            'shipping_method' => 'método de envío',
            'tracking_number' => 'número de seguimiento',
            'expected_delivery_date' => 'fecha de entrega esperada',
            'discount_code' => 'código de descuento',
            'organization_id' => 'organización',
            'items' => 'items del pedido',
            'tags' => 'etiquetas',
            'billing_address' => 'dirección de facturación',
            'shipping_address' => 'dirección de envío',
            'custom_fields' => 'campos personalizados',
            'attachments' => 'archivos adjuntos',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir strings booleanos a booleanos reales
        if ($this->has('is_urgent')) {
            $this->merge([
                'is_urgent' => filter_var($this->is_urgent, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        // Limpiar arrays vacíos
        if ($this->has('tags') && empty($this->tags)) {
            $this->merge(['tags' => null]);
        }

        if ($this->has('attachments') && empty($this->attachments)) {
            $this->merge(['attachments' => null]);
        }

        // Limpiar direcciones si están vacías
        if ($this->has('billing_address') && empty($this->billing_address)) {
            $this->merge(['billing_address' => null]);
        }

        if ($this->has('shipping_address') && empty($this->shipping_address)) {
            $this->merge(['shipping_address' => null]);
        }

        if ($this->has('custom_fields') && empty($this->custom_fields)) {
            $this->merge(['custom_fields' => null]);
        }

        // Generar número de pedido si no se proporciona
        if (!$this->has('order_number') || empty($this->order_number)) {
            $this->merge([
                'order_number' => $this->generateOrderNumber()
            ]);
        }
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $counter = \App\Models\SaleOrder::whereDate('created_at', today())->count() + 1;
        
        return "{$prefix}-{$date}-" . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }
}
