<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserDeviceResource",
 *     title="User Device Resource",
 *     description="Recurso de dispositivo de usuario",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="device_name", type="string", example="iPhone 15 Pro"),
 *     @OA\Property(property="device_type", type="string", example="mobile"),
 *     @OA\Property(property="platform", type="string", example="ios"),
 *     @OA\Property(property="browser", type="string", example="Safari"),
 *     @OA\Property(property="browser_version", type="string", example="17.2"),
 *     @OA\Property(property="os_version", type="string", example="iOS 17.2.1"),
 *     @OA\Property(property="is_current", type="boolean", example=true),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="has_push_token", type="boolean", example=true),
 *     @OA\Property(property="device_info", type="string", example="iPhone 15 Pro (iOS 17.2.1)"),
 *     @OA\Property(property="is_recently_active", type="boolean", example=true),
 *     @OA\Property(property="ip_address", type="string", example="192.168.1.100"),
 *     @OA\Property(property="user_agent", type="string", example="Mozilla/5.0..."),
 *     @OA\Property(property="last_seen_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="revoked_at", type="string", format="date-time", nullable=true, example=null)
 * )
 */
class UserDeviceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_name' => $this->device_name,
            'device_type' => $this->device_type,
            'device_type_name' => $this->device_type_name,
            'platform' => $this->platform,
            'is_current' => $this->is_current,
            'is_active' => $this->isActive(),
            'is_revoked' => $this->isRevoked(),
            'has_push_token' => !empty($this->push_token),
            'device_info' => $this->device_info,
            'is_recently_active' => $this->isRecentlyActive(),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'last_seen_at' => $this->last_seen_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'revoked_at' => $this->revoked_at,
        ];
    }
}