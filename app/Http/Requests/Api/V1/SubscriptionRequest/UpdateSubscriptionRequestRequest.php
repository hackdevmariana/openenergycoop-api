<?php

namespace App\Http\Requests\Api\V1\SubscriptionRequest;

use App\Models\SubscriptionRequest;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateSubscriptionRequestRequest",
 *     title="Update Subscription Request",
 *     description="Datos para actualizar una solicitud de suscripción existente",
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID del usuario que hace la solicitud",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="cooperative_id",
 *         type="integer",
 *         description="ID de la cooperativa/organización",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Tipo de solicitud",
 *         enum={"new_subscription", "ownership_change", "tenant_request"},
 *         example="new_subscription"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Estado de la solicitud",
 *         enum={"pending", "approved", "rejected", "in_review"},
 *         example="pending"
 *     ),
 *     @OA\Property(
 *         property="notes",
 *         type="string",
 *         description="Notas adicionales sobre la solicitud",
 *         example="Solicitud de alta para nueva vivienda"
 *     )
 * )
 */
class UpdateSubscriptionRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('subscription_request'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|exists:users,id',
            'cooperative_id' => 'sometimes|integer|exists:organizations,id',
            'type' => 'sometimes|string|in:' . implode(',', [
                SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                SubscriptionRequest::TYPE_TENANT_REQUEST,
            ]),
            'status' => 'sometimes|string|in:' . implode(',', [
                SubscriptionRequest::STATUS_PENDING,
                SubscriptionRequest::STATUS_APPROVED,
                SubscriptionRequest::STATUS_REJECTED,
                SubscriptionRequest::STATUS_IN_REVIEW,
            ]),
            'notes' => 'sometimes|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'cooperative_id.exists' => 'La cooperativa seleccionada no existe.',
            'type.in' => 'El tipo de solicitud debe ser válido.',
            'status.in' => 'El estado de la solicitud debe ser válido.',
            'notes.max' => 'Las notas no pueden tener más de 1000 caracteres.',
        ];
    }
}
