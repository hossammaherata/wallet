<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ProfileResource
 * 
 * API Resource for transforming User profile data, including wallet and notifications.
 * 
 * @package App\Http\Resources
 */
class ProfileResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'type' => $this->type,
            'status' => $this->status ?? 'active',
            'wallet' => new WalletResource($this->whenLoaded('wallet')),
            'unread_notifications_count' => $this->unreadNotificationsCount(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}

