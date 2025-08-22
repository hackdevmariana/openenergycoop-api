<?php

namespace App\Http\Requests\Api\V1\DiscountCode;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiscountCodeRequest extends FormRequest
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
            'code' => 'required|string|max:50|unique:discount_codes,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => [
                'required',
                'string',
                Rule::in(['percentage', 'fixed_amount', 'free_shipping', 'buy_one_get_one', 'other'])
            ],
            'value' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'expired', 'suspended'])
            ],
            'is_public' => 'boolean',
            'is_first_time_only' => 'boolean',
            'is_new_customer_only' => 'boolean',
            'is_returning_customer_only' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'organization_id' => 'nullable|exists:organizations,id',
            'applicable_products' => 'nullable|array',
            'applicable_products.*' => 'integer|exists:products,id',
            'applicable_categories' => 'nullable|array',
            'applicable_categories.*' => 'integer|exists:categories,id',
            'excluded_products' => 'nullable|array',
            'excluded_products.*' => 'integer|exists:products,id',
            'excluded_categories' => 'nullable|array',
            'excluded_categories.*' => 'integer|exists:categories,id',
            'notes' => 'nullable|string|max:2000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'terms_conditions' => 'nullable|string|max:2000',
            'restrictions' => 'nullable|string|max:2000',
            'auto_apply' => 'boolean',
            'priority' => 'nullable|integer|min:1|max:100',
            'is_stackable' => 'boolean',
            'max_stack_count' => 'nullable|integer|min:1',
            'customer_groups' => 'nullable|array',
            'customer_groups.*' => 'string|max:100',
            'geographic_restrictions' => 'nullable|array',
            'geographic_restrictions.countries' => 'nullable|array',
            'geographic_restrictions.countries.*' => 'string|max:100',
            'geographic_restrictions.states' => 'nullable|array',
            'geographic_restrictions.states.*' => 'string|max:100',
            'geographic_restrictions.cities' => 'nullable|array',
            'geographic_restrictions.cities.*' => 'string|max:100',
            'time_restrictions' => 'nullable|array',
            'time_restrictions.days_of_week' => 'nullable|array',
            'time_restrictions.days_of_week.*' => 'integer|between:1,7',
            'time_restrictions.hours_of_day' => 'nullable|array',
            'time_restrictions.hours_of_day.*' => 'integer|between:0,23',
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
            'code.required' => 'El código de descuento es obligatorio.',
            'code.max' => 'El código de descuento no puede tener más de 50 caracteres.',
            'code.unique' => 'El código de descuento ya está registrado.',
            'name.required' => 'El nombre del descuento es obligatorio.',
            'name.max' => 'El nombre del descuento no puede tener más de 255 caracteres.',
            'description.max' => 'La descripción no puede tener más de 2000 caracteres.',
            'type.required' => 'El tipo de descuento es obligatorio.',
            'type.in' => 'El tipo de descuento debe ser uno de: percentage, fixed_amount, free_shipping, buy_one_get_one, other.',
            'value.required' => 'El valor del descuento es obligatorio.',
            'value.numeric' => 'El valor del descuento debe ser un número.',
            'value.min' => 'El valor del descuento debe ser mayor o igual a 0.',
            'max_discount_amount.numeric' => 'El monto máximo de descuento debe ser un número.',
            'max_discount_amount.min' => 'El monto máximo de descuento debe ser mayor o igual a 0.',
            'min_order_amount.numeric' => 'El monto mínimo de orden debe ser un número.',
            'min_order_amount.min' => 'El monto mínimo de orden debe ser mayor o igual a 0.',
            'max_order_amount.numeric' => 'El monto máximo de orden debe ser un número.',
            'max_order_amount.min' => 'El monto máximo de orden debe ser mayor o igual a 0.',
            'usage_limit.integer' => 'El límite de uso debe ser un número entero.',
            'usage_limit.min' => 'El límite de uso debe ser mayor o igual a 1.',
            'usage_limit_per_user.integer' => 'El límite de uso por usuario debe ser un número entero.',
            'usage_limit_per_user.min' => 'El límite de uso por usuario debe ser mayor o igual a 1.',
            'status.required' => 'El estado del descuento es obligatorio.',
            'status.in' => 'El estado del descuento debe ser uno de: active, inactive, expired, suspended.',
            'is_public.boolean' => 'El estado público debe ser verdadero o falso.',
            'is_first_time_only.boolean' => 'El estado de solo primera vez debe ser verdadero o falso.',
            'is_new_customer_only.boolean' => 'El estado de solo nuevos clientes debe ser verdadero o falso.',
            'is_returning_customer_only.boolean' => 'El estado de solo clientes recurrentes debe ser verdadero o falso.',
            'valid_from.date' => 'La fecha de inicio de validez debe ser una fecha válida.',
            'valid_until.date' => 'La fecha de fin de validez debe ser una fecha válida.',
            'valid_until.after' => 'La fecha de fin de validez debe ser posterior a la fecha de inicio.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'applicable_products.array' => 'Los productos aplicables deben ser un array.',
            'applicable_products.*.integer' => 'Cada ID de producto debe ser un número entero.',
            'applicable_products.*.exists' => 'El producto seleccionado no existe.',
            'applicable_categories.array' => 'Las categorías aplicables deben ser un array.',
            'applicable_categories.*.integer' => 'Cada ID de categoría debe ser un número entero.',
            'applicable_categories.*.exists' => 'La categoría seleccionada no existe.',
            'excluded_products.array' => 'Los productos excluidos deben ser un array.',
            'excluded_products.*.integer' => 'Cada ID de producto debe ser un número entero.',
            'excluded_products.*.exists' => 'El producto seleccionado no existe.',
            'excluded_categories.array' => 'Las categorías excluidas deben ser un array.',
            'excluded_categories.*.integer' => 'Cada ID de categoría debe ser un número entero.',
            'excluded_categories.*.exists' => 'La categoría seleccionada no existe.',
            'notes.max' => 'Las notas no pueden tener más de 2000 caracteres.',
            'tags.array' => 'Las etiquetas deben ser un array.',
            'tags.*.string' => 'Cada etiqueta debe ser una cadena de texto.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 50 caracteres.',
            'terms_conditions.max' => 'Los términos y condiciones no pueden tener más de 2000 caracteres.',
            'restrictions.max' => 'Las restricciones no pueden tener más de 2000 caracteres.',
            'auto_apply.boolean' => 'El estado de aplicación automática debe ser verdadero o falso.',
            'priority.integer' => 'La prioridad debe ser un número entero.',
            'priority.min' => 'La prioridad debe ser mayor o igual a 1.',
            'priority.max' => 'La prioridad debe ser menor o igual a 100.',
            'is_stackable.boolean' => 'El estado de apilable debe ser verdadero o falso.',
            'max_stack_count.integer' => 'El número máximo de apilación debe ser un número entero.',
            'max_stack_count.min' => 'El número máximo de apilación debe ser mayor o igual a 1.',
            'customer_groups.array' => 'Los grupos de clientes deben ser un array.',
            'customer_groups.*.string' => 'Cada grupo de clientes debe ser una cadena de texto.',
            'customer_groups.*.max' => 'Cada grupo de clientes no puede tener más de 100 caracteres.',
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
            'time_restrictions.days_of_week.*.between' => 'Cada día de la semana debe estar entre 1 y 7.',
            'time_restrictions.hours_of_day.array' => 'Las horas del día deben ser un array.',
            'time_restrictions.hours_of_day.*.integer' => 'Cada hora del día debe ser un número entero.',
            'time_restrictions.hours_of_day.*.between' => 'Cada hora del día debe estar entre 0 y 23.',
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
            'code' => 'código de descuento',
            'name' => 'nombre del descuento',
            'description' => 'descripción',
            'type' => 'tipo de descuento',
            'value' => 'valor del descuento',
            'max_discount_amount' => 'monto máximo de descuento',
            'min_order_amount' => 'monto mínimo de orden',
            'max_order_amount' => 'monto máximo de orden',
            'usage_limit' => 'límite de uso',
            'usage_limit_per_user' => 'límite de uso por usuario',
            'status' => 'estado del descuento',
            'is_public' => 'estado público',
            'is_first_time_only' => 'solo primera vez',
            'is_new_customer_only' => 'solo nuevos clientes',
            'is_returning_customer_only' => 'solo clientes recurrentes',
            'valid_from' => 'fecha de inicio de validez',
            'valid_until' => 'fecha de fin de validez',
            'organization_id' => 'organización',
            'applicable_products' => 'productos aplicables',
            'applicable_categories' => 'categorías aplicables',
            'excluded_products' => 'productos excluidos',
            'excluded_categories' => 'categorías excluidas',
            'notes' => 'notas',
            'tags' => 'etiquetas',
            'terms_conditions' => 'términos y condiciones',
            'restrictions' => 'restricciones',
            'auto_apply' => 'aplicación automática',
            'priority' => 'prioridad',
            'is_stackable' => 'apilable',
            'max_stack_count' => 'número máximo de apilación',
            'customer_groups' => 'grupos de clientes',
            'geographic_restrictions' => 'restricciones geográficas',
            'time_restrictions' => 'restricciones de tiempo',
            'attachments' => 'archivos adjuntos',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir strings booleanos a booleanos reales
        $booleanFields = [
            'is_public', 'is_first_time_only', 'is_new_customer_only', 
            'is_returning_customer_only', 'auto_apply', 'is_stackable'
        ];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->$field, FILTER_VALIDATE_BOOLEAN)
                ]);
            }
        }

        // Limpiar arrays vacíos
        $arrayFields = [
            'tags', 'applicable_products', 'applicable_categories',
            'excluded_products', 'excluded_categories', 'customer_groups',
            'attachments'
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field) && empty($this->$field)) {
                $this->merge([$field => null]);
            }
        }

        // Limpiar restricciones geográficas y de tiempo si están vacías
        if ($this->has('geographic_restrictions') && empty($this->geographic_restrictions)) {
            $this->merge(['geographic_restrictions' => null]);
        }

        if ($this->has('time_restrictions') && empty($this->time_restrictions)) {
            $this->merge(['time_restrictions' => null]);
        }
    }
}
