<?php

namespace App\Http\Requests\Api\V1\PreSaleOffer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreSaleOfferRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:2000',
            'type' => [
                'sometimes',
                'string',
                Rule::in(['discount', 'fixed_amount', 'free_shipping', 'buy_one_get_one', 'other'])
            ],
            'discount_percentage' => 'sometimes|nullable|numeric|min:0|max:100',
            'fixed_discount_amount' => 'sometimes|nullable|numeric|min:0',
            'original_price' => 'sometimes|nullable|numeric|min:0',
            'price' => 'sometimes|nullable|numeric|min:0',
            'currency' => 'sometimes|nullable|string|max:3',
            'status' => [
                'sometimes',
                'string',
                Rule::in(['active', 'inactive', 'draft', 'expired'])
            ],
            'is_featured' => 'sometimes|boolean',
            'is_limited_time' => 'sometimes|boolean',
            'start_date' => 'sometimes|nullable|date|after_or_equal:today',
            'end_date' => 'sometimes|nullable|date|after:start_date',
            'max_quantity' => 'sometimes|nullable|integer|min:1',
            'min_quantity' => 'sometimes|nullable|integer|min:1',
            'product_id' => 'sometimes|nullable|exists:products,id',
            'product_name' => 'sometimes|nullable|string|max:255',
            'terms_conditions' => 'sometimes|nullable|string|max:3000',
            'organization_id' => 'sometimes|nullable|exists:organizations,id',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:50',
            'requirements' => 'sometimes|nullable|array',
            'requirements.*' => 'string|max:200',
            'benefits' => 'sometimes|nullable|array',
            'benefits.*' => 'string|max:200',
            'restrictions' => 'sometimes|nullable|array',
            'restrictions.*' => 'string|max:200',
            'custom_fields' => 'sometimes|nullable|array',
            'custom_fields.*' => 'string|max:500',
            'attachments' => 'sometimes|nullable|array',
            'attachments.*' => 'string|max:255',
            'priority' => 'sometimes|nullable|integer|min:1|max:100',
            'is_stackable' => 'sometimes|boolean',
            'max_stack_count' => 'sometimes|nullable|integer|min:1|max:10',
            'customer_groups' => 'sometimes|nullable|array',
            'customer_groups.*' => 'string|max:100',
            'geographic_restrictions' => 'sometimes|nullable|array',
            'geographic_restrictions.countries' => 'nullable|array',
            'geographic_restrictions.countries.*' => 'string|max:100',
            'geographic_restrictions.states' => 'nullable|array',
            'geographic_restrictions.states.*' => 'string|max:100',
            'geographic_restrictions.cities' => 'nullable|array',
            'geographic_restrictions.cities.*' => 'string|max:100',
            'time_restrictions' => 'sometimes|nullable|array',
            'time_restrictions.days_of_week' => 'nullable|array',
            'time_restrictions.days_of_week.*' => 'integer|min:1|max:7',
            'time_restrictions.hours_of_day' => 'nullable|array',
            'time_restrictions.hours_of_day.*' => 'integer|min:0|max:23',
            'applicable_products' => 'sometimes|nullable|array',
            'applicable_products.*' => 'string|max:255',
            'applicable_categories' => 'sometimes|nullable|array',
            'applicable_categories.*' => 'string|max:255',
            'excluded_products' => 'sometimes|nullable|array',
            'excluded_products.*' => 'string|max:255',
            'excluded_categories' => 'sometimes|nullable|array',
            'excluded_categories.*' => 'string|max:255',
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
            'title.max' => 'El título de la oferta no puede tener más de 255 caracteres.',
            'description.max' => 'La descripción no puede tener más de 2000 caracteres.',
            'type.in' => 'El tipo de oferta debe ser uno de: discount, fixed_amount, free_shipping, buy_one_get_one, other.',
            'discount_percentage.numeric' => 'El porcentaje de descuento debe ser un número.',
            'discount_percentage.min' => 'El porcentaje de descuento debe ser mayor o igual a 0.',
            'discount_percentage.max' => 'El porcentaje de descuento no puede ser mayor a 100.',
            'fixed_discount_amount.numeric' => 'El monto de descuento fijo debe ser un número.',
            'fixed_discount_amount.min' => 'El monto de descuento fijo debe ser mayor o igual a 0.',
            'original_price.numeric' => 'El precio original debe ser un número.',
            'original_price.min' => 'El precio original debe ser mayor o igual a 0.',
            'price.numeric' => 'El precio de la oferta debe ser un número.',
            'price.min' => 'El precio de la oferta debe ser mayor o igual a 0.',
            'currency.max' => 'El código de moneda no puede tener más de 3 caracteres.',
            'status.in' => 'El estado de la oferta debe ser uno de: active, inactive, draft, expired.',
            'is_featured.boolean' => 'El estado destacado debe ser verdadero o falso.',
            'is_limited_time.boolean' => 'El estado de tiempo limitado debe ser verdadero o falso.',
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida.',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser igual o posterior a hoy.',
            'end_date.date' => 'La fecha de fin debe ser una fecha válida.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'max_quantity.integer' => 'La cantidad máxima debe ser un número entero.',
            'max_quantity.min' => 'La cantidad máxima debe ser mayor o igual a 1.',
            'min_quantity.integer' => 'La cantidad mínima debe ser un número entero.',
            'min_quantity.min' => 'La cantidad mínima debe ser mayor o igual a 1.',
            'product_id.exists' => 'El producto seleccionado no existe.',
            'product_name.max' => 'El nombre del producto no puede tener más de 255 caracteres.',
            'terms_conditions.max' => 'Los términos y condiciones no pueden tener más de 3000 caracteres.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'tags.array' => 'Las etiquetas deben ser un array.',
            'tags.*.string' => 'Cada etiqueta debe ser una cadena de texto.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 50 caracteres.',
            'requirements.array' => 'Los requisitos deben ser un array.',
            'requirements.*.string' => 'Cada requisito debe ser una cadena de texto.',
            'requirements.*.max' => 'Cada requisito no puede tener más de 200 caracteres.',
            'benefits.array' => 'Los beneficios deben ser un array.',
            'benefits.*.string' => 'Cada beneficio debe ser una cadena de texto.',
            'benefits.*.max' => 'Cada beneficio no puede tener más de 200 caracteres.',
            'restrictions.array' => 'Las restricciones deben ser un array.',
            'restrictions.*.string' => 'Cada restricción debe ser una cadena de texto.',
            'restrictions.*.max' => 'Cada restricción no puede tener más de 200 caracteres.',
            'custom_fields.array' => 'Los campos personalizados deben ser un array.',
            'custom_fields.*.string' => 'Cada campo personalizado debe ser una cadena de texto.',
            'custom_fields.*.max' => 'Cada campo personalizado no puede tener más de 500 caracteres.',
            'attachments.array' => 'Los archivos adjuntos deben ser un array.',
            'attachments.*.string' => 'Cada archivo adjunto debe ser una cadena de texto.',
            'attachments.*.max' => 'Cada archivo adjunto no puede tener más de 255 caracteres.',
            'priority.integer' => 'La prioridad debe ser un número entero.',
            'priority.min' => 'La prioridad debe ser mayor o igual a 1.',
            'priority.max' => 'La prioridad no puede ser mayor a 100.',
            'is_stackable.boolean' => 'El estado apilable debe ser verdadero o falso.',
            'max_stack_count.integer' => 'El número máximo de apilación debe ser un número entero.',
            'max_stack_count.min' => 'El número máximo de apilación debe ser mayor o igual a 1.',
            'max_stack_count.max' => 'El número máximo de apilación no puede ser mayor a 10.',
            'customer_groups.array' => 'Los grupos de clientes deben ser un array.',
            'customer_groups.*.string' => 'Cada grupo de cliente debe ser una cadena de texto.',
            'customer_groups.*.max' => 'Cada grupo de cliente no puede tener más de 100 caracteres.',
            'geographic_restrictions.array' => 'Las restricciones geográficas deben ser un array.',
            'geographic_restrictions.countries.array' => 'Los países deben ser un array.',
            'geographic_restrictions.countries.*.string' => 'Cada país debe ser una cadena de texto.',
            'geographic_restrictions.countries.*.max' => 'Cada país no puede tener más de 100 caracteres.',
            'geographic_restrictions.states.array' => 'Los estados deben ser un array.',
            'geographic_restrictions.states.*.string' => 'Cada estado debe ser una cadena de texto.',
            'geographic_restrictions.states.*.max' => 'Cada estado no puede tener más de 100 caracteres.',
            'geographic_restrictions.cities.array' => 'Las ciudades deben ser un array.',
            'geographic_restrictions.cities.*.string' => 'Cada ciudad debe ser una cadena de texto.',
            'geographic_restrictions.cities.*.max' => 'Cada ciudad no puede tener más de 100 caracteres.',
            'time_restrictions.array' => 'Las restricciones de tiempo deben ser un array.',
            'time_restrictions.days_of_week.array' => 'Los días de la semana deben ser un array.',
            'time_restrictions.days_of_week.*.integer' => 'Cada día de la semana debe ser un número entero.',
            'time_restrictions.days_of_week.*.min' => 'Cada día de la semana debe ser mayor o igual a 1.',
            'time_restrictions.days_of_week.*.max' => 'Cada día de la semana no puede ser mayor a 7.',
            'time_restrictions.hours_of_day.array' => 'Las horas del día deben ser un array.',
            'time_restrictions.hours_of_day.*.integer' => 'Cada hora del día debe ser un número entero.',
            'time_restrictions.hours_of_day.*.min' => 'Cada hora del día debe ser mayor o igual a 0.',
            'time_restrictions.hours_of_day.*.max' => 'Cada hora del día no puede ser mayor a 23.',
            'applicable_products.array' => 'Los productos aplicables deben ser un array.',
            'applicable_products.*.string' => 'Cada producto aplicable debe ser una cadena de texto.',
            'applicable_products.*.max' => 'Cada producto aplicable no puede tener más de 255 caracteres.',
            'applicable_categories.array' => 'Las categorías aplicables deben ser un array.',
            'applicable_categories.*.string' => 'Cada categoría aplicable debe ser una cadena de texto.',
            'applicable_categories.*.max' => 'Cada categoría aplicable no puede tener más de 255 caracteres.',
            'excluded_products.array' => 'Los productos excluidos deben ser un array.',
            'excluded_products.*.string' => 'Cada producto excluido debe ser una cadena de texto.',
            'excluded_products.*.max' => 'Cada producto excluido no puede tener más de 255 caracteres.',
            'excluded_categories.array' => 'Las categorías excluidas deben ser un array.',
            'excluded_categories.*.string' => 'Cada categoría excluida debe ser una cadena de texto.',
            'excluded_categories.*.max' => 'Cada categoría excluida no puede tener más de 255 caracteres.',
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
            'title' => 'título de la oferta',
            'description' => 'descripción',
            'type' => 'tipo de oferta',
            'discount_percentage' => 'porcentaje de descuento',
            'fixed_discount_amount' => 'monto de descuento fijo',
            'original_price' => 'precio original',
            'price' => 'precio de la oferta',
            'currency' => 'moneda',
            'status' => 'estado de la oferta',
            'is_featured' => 'estado destacado',
            'is_limited_time' => 'estado de tiempo limitado',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
            'max_quantity' => 'cantidad máxima',
            'min_quantity' => 'cantidad mínima',
            'product_id' => 'producto',
            'product_name' => 'nombre del producto',
            'terms_conditions' => 'términos y condiciones',
            'organization_id' => 'organización',
            'tags' => 'etiquetas',
            'requirements' => 'requisitos',
            'benefits' => 'beneficios',
            'restrictions' => 'restricciones',
            'custom_fields' => 'campos personalizados',
            'attachments' => 'archivos adjuntos',
            'priority' => 'prioridad',
            'is_stackable' => 'estado apilable',
            'max_stack_count' => 'número máximo de apilación',
            'customer_groups' => 'grupos de clientes',
            'geographic_restrictions' => 'restricciones geográficas',
            'time_restrictions' => 'restricciones de tiempo',
            'applicable_products' => 'productos aplicables',
            'applicable_categories' => 'categorías aplicables',
            'excluded_products' => 'productos excluidos',
            'excluded_categories' => 'categorías excluidas',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir strings booleanos a booleanos reales
        if ($this->has('is_featured')) {
            $this->merge([
                'is_featured' => filter_var($this->is_featured, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        if ($this->has('is_limited_time')) {
            $this->merge([
                'is_limited_time' => filter_var($this->is_limited_time, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        if ($this->has('is_stackable')) {
            $this->merge([
                'is_stackable' => filter_var($this->is_stackable, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        // Limpiar arrays vacíos
        if ($this->has('tags') && empty($this->tags)) {
            $this->merge(['tags' => null]);
        }

        if ($this->has('requirements') && empty($this->requirements)) {
            $this->merge(['requirements' => null]);
        }

        if ($this->has('benefits') && empty($this->benefits)) {
            $this->merge(['benefits' => null]);
        }

        if ($this->has('restrictions') && empty($this->restrictions)) {
            $this->merge(['restrictions' => null]);
        }

        if ($this->has('attachments') && empty($this->attachments)) {
            $this->merge(['attachments' => null]);
        }

        if ($this->has('custom_fields') && empty($this->custom_fields)) {
            $this->merge(['custom_fields' => null]);
        }

        if ($this->has('customer_groups') && empty($this->customer_groups)) {
            $this->merge(['customer_groups' => null]);
        }

        if ($this->has('applicable_products') && empty($this->applicable_products)) {
            $this->merge(['applicable_products' => null]);
        }

        if ($this->has('applicable_categories') && empty($this->applicable_categories)) {
            $this->merge(['applicable_categories' => null]);
        }

        if ($this->has('excluded_products') && empty($this->excluded_products)) {
            $this->merge(['excluded_products' => null]);
        }

        if ($this->has('excluded_categories') && empty($this->excluded_categories)) {
            $this->merge(['excluded_categories' => null]);
        }

        // Limpiar restricciones geográficas si están vacías
        if ($this->has('geographic_restrictions')) {
            $geoRestrictions = $this->geographic_restrictions;
            if (empty($geoRestrictions) || 
                (empty($geoRestrictions['countries']) && 
                 empty($geoRestrictions['states']) && 
                 empty($geoRestrictions['cities']))) {
                $this->merge(['geographic_restrictions' => null]);
            }
        }

        // Limpiar restricciones de tiempo si están vacías
        if ($this->has('time_restrictions')) {
            $timeRestrictions = $this->time_restrictions;
            if (empty($timeRestrictions) || 
                (empty($timeRestrictions['days_of_week']) && 
                 empty($timeRestrictions['hours_of_day']))) {
                $this->merge(['time_restrictions' => null]);
            }
        }

        // Validar que si es tiempo limitado, tenga fechas
        if ($this->boolean('is_limited_time')) {
            if (!$this->filled('start_date') || !$this->filled('end_date')) {
                $this->merge(['is_limited_time' => false]);
            }
        }

        // Validar que si es destacado, esté activo
        if ($this->boolean('is_featured') && $this->get('status') !== 'active') {
            $this->merge(['is_featured' => false]);
        }
    }
}
