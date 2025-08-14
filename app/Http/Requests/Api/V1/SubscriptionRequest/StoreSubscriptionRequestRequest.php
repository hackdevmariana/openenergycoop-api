<?php

namespace App\Http\Requests\Api\V1\SubscriptionRequest;

use App\Models\SubscriptionRequest;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreSubscriptionRequestRequest",
 *     title="Store Subscription Request",
 *     description="Datos para crear una nueva solicitud de suscripción",
 *     required={"user_id", "cooperative_id", "type"},
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
class StoreSubscriptionRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', SubscriptionRequest::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'cooperative_id' => 'required|integer|exists:organizations,id',
            'type' => 'required|string|in:' . implode(',', [
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
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'cooperative_id.required' => 'La cooperativa es obligatoria.',
            'cooperative_id.exists' => 'La cooperativa seleccionada no existe.',
            'type.required' => 'El tipo de solicitud es obligatorio.',
            'type.in' => 'El tipo de solicitud debe ser válido.',
            'status.in' => 'El estado de la solicitud debe ser válido.',
            'notes.max' => 'Las notas no pueden tener más de 1000 caracteres.',
        ];
    }
}
