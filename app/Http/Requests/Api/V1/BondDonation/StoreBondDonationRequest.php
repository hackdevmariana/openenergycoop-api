<?php

namespace App\Http\Requests\Api\V1\BondDonation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBondDonationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'donor_id' => ['nullable', 'exists:users,id'],
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_email' => ['required', 'email', 'max:255'],
            'donor_phone' => ['nullable', 'string', 'max:20'],
            'donor_address' => ['nullable', 'string', 'max:500'],
            'donor_city' => ['nullable', 'string', 'max:100'],
            'donor_state' => ['nullable', 'string', 'max:100'],
            'donor_country' => ['nullable', 'string', 'max:100'],
            'donor_postal_code' => ['nullable', 'string', 'max:20'],
            'energy_bond_id' => ['required', 'exists:energy_bonds,id'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'donation_type' => ['required', 'string', Rule::in([
                'one_time', 'recurring', 'matching', 'challenge', 'memorial', 'honor', 'corporate', 'foundation', 'other'
            ])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'currency' => ['required', 'string', 'max:3', 'default:USD'],
            'payment_method' => ['required', 'string', Rule::in([
                'credit_card', 'debit_card', 'bank_transfer', 'paypal', 'stripe', 'check', 'cash', 'crypto', 'other'
            ])],
            'payment_status' => ['required', 'string', Rule::in([
                'pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'
            ])],
            'status' => ['required', 'string', Rule::in([
                'pending', 'confirmed', 'processed', 'rejected', 'refunded', 'cancelled'
            ])],
            'is_anonymous' => ['boolean'],
            'is_public' => ['boolean'],
            'message' => ['nullable', 'string', 'max:1000'],
            'dedication_name' => ['nullable', 'string', 'max:255'],
            'dedication_message' => ['nullable', 'string', 'max:500'],
            'recurring_frequency' => ['nullable', 'string', Rule::in([
                'weekly', 'monthly', 'quarterly', 'annually'
            ])],
            'recurring_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'recurring_end_date' => ['nullable', 'date', 'after:recurring_start_date'],
            'tax_receipt_required' => ['boolean'],
            'tax_receipt_sent' => ['boolean'],
            'tax_receipt_number' => ['nullable', 'string', 'max:100'],
            'tax_receipt_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,mp4,avi,mov', 'max:10240'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'donor_name.required' => 'El nombre del donante es obligatorio.',
            'donor_name.max' => 'El nombre del donante no puede exceder 255 caracteres.',
            'donor_email.required' => 'El email del donante es obligatorio.',
            'donor_email.email' => 'El email del donante debe ser válido.',
            'donor_email.max' => 'El email del donante no puede exceder 255 caracteres.',
            'donor_phone.max' => 'El teléfono del donante no puede exceder 20 caracteres.',
            'donor_address.max' => 'La dirección del donante no puede exceder 500 caracteres.',
            'donor_city.max' => 'La ciudad del donante no puede exceder 100 caracteres.',
            'donor_state.max' => 'El estado del donante no puede exceder 100 caracteres.',
            'donor_country.max' => 'El país del donante no puede exceder 100 caracteres.',
            'donor_postal_code.max' => 'El código postal del donante no puede exceder 20 caracteres.',
            'donor_id.exists' => 'El usuario donante no existe.',
            'energy_bond_id.required' => 'El bono de energía es obligatorio.',
            'energy_bond_id.exists' => 'El bono de energía seleccionado no existe.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'campaign_id.exists' => 'La campaña seleccionada no existe.',
            'donation_type.required' => 'El tipo de donación es obligatorio.',
            'donation_type.in' => 'El tipo de donación seleccionado no es válido.',
            'amount.required' => 'El monto de la donación es obligatorio.',
            'amount.numeric' => 'El monto debe ser un número.',
            'amount.min' => 'El monto debe ser al menos 0.01.',
            'amount.max' => 'El monto no puede exceder 999,999.99.',
            'currency.required' => 'La moneda es obligatoria.',
            'currency.max' => 'La moneda no puede exceder 3 caracteres.',
            'payment_method.required' => 'El método de pago es obligatorio.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',
            'payment_status.required' => 'El estado del pago es obligatorio.',
            'payment_status.in' => 'El estado del pago seleccionado no es válido.',
            'status.required' => 'El estado de la donación es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            'message.max' => 'El mensaje no puede exceder 1000 caracteres.',
            'dedication_name.max' => 'El nombre de la dedicatoria no puede exceder 255 caracteres.',
            'dedication_message.max' => 'El mensaje de la dedicatoria no puede exceder 500 caracteres.',
            'recurring_frequency.in' => 'La frecuencia recurrente seleccionada no es válida.',
            'recurring_start_date.date' => 'La fecha de inicio recurrente debe ser una fecha válida.',
            'recurring_start_date.after_or_equal' => 'La fecha de inicio recurrente debe ser hoy o posterior.',
            'recurring_end_date.date' => 'La fecha de fin recurrente debe ser una fecha válida.',
            'recurring_end_date.after' => 'La fecha de fin recurrente debe ser posterior a la fecha de inicio.',
            'tax_receipt_number.max' => 'El número de recibo fiscal no puede exceder 100 caracteres.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.',
            'internal_notes.max' => 'Las notas internas no pueden exceder 1000 caracteres.',
            'tags.array' => 'Las etiquetas deben ser un array.',
            'tags.*.string' => 'Cada etiqueta debe ser una cadena de texto.',
            'tags.*.max' => 'Cada etiqueta no puede exceder 100 caracteres.',
            'attachments.array' => 'Los archivos adjuntos deben ser un array.',
            'attachments.*.file' => 'Cada archivo adjunto debe ser un archivo válido.',
            'attachments.*.mimes' => 'Los archivos adjuntos deben ser de tipo: pdf, doc, docx, jpg, jpeg, png, mp4, avi, mov.',
            'attachments.*.max' => 'Cada archivo adjunto no puede exceder 10MB.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'donor_id' => 'usuario donante',
            'donor_name' => 'nombre del donante',
            'donor_email' => 'email del donante',
            'donor_phone' => 'teléfono del donante',
            'donor_address' => 'dirección del donante',
            'donor_city' => 'ciudad del donante',
            'donor_state' => 'estado del donante',
            'donor_country' => 'país del donante',
            'donor_postal_code' => 'código postal del donante',
            'energy_bond_id' => 'bono de energía',
            'organization_id' => 'organización',
            'campaign_id' => 'campaña',
            'donation_type' => 'tipo de donación',
            'amount' => 'monto',
            'currency' => 'moneda',
            'payment_method' => 'método de pago',
            'payment_status' => 'estado del pago',
            'status' => 'estado',
            'is_anonymous' => 'es anónimo',
            'is_public' => 'es público',
            'message' => 'mensaje',
            'dedication_name' => 'nombre de la dedicatoria',
            'dedication_message' => 'mensaje de la dedicatoria',
            'recurring_frequency' => 'frecuencia recurrente',
            'recurring_start_date' => 'fecha de inicio recurrente',
            'recurring_end_date' => 'fecha de fin recurrente',
            'tax_receipt_required' => 'recibo fiscal requerido',
            'tax_receipt_sent' => 'recibo fiscal enviado',
            'tax_receipt_number' => 'número de recibo fiscal',
            'tax_receipt_date' => 'fecha del recibo fiscal',
            'notes' => 'notas',
            'internal_notes' => 'notas internas',
            'tags' => 'etiquetas',
            'attachments' => 'archivos adjuntos',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_anonymous' => $this->boolean('is_anonymous'),
            'is_public' => $this->boolean('is_public'),
            'tax_receipt_required' => $this->boolean('tax_receipt_required'),
            'tax_receipt_sent' => $this->boolean('tax_receipt_sent'),
        ]);
    }
}
