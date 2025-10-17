<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'roles' => $this->resolveRoles(),
            'email' => $this->email,
            'phone' => $this->phone,
            'fname' => $this->fname,
            'lname' => $this->lname,
            'gender' => $this->gender,
            'status' => $this->status,
            'status_label' => $this->resolveStatusLabel(),
            'kycStatus' => $this->resolveKycStatus(),
            'phone_verified_at' => $this->phone_verified_at?->toIso8601String(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function resolveRoles(): array
    {
        $roles = [];

        if ((int) $this->is_vendor === 2) {
            $roles[] = 'vendor';
        } else {
            $roles[] = 'customer';
        }

        if ((int) $this->is_provider === 1) {
            $roles[] = 'provider';
        }

        return array_values(array_unique($roles));
    }

    private function resolveStatusLabel(): string
    {
        return match ((int) $this->status) {
            1 => 'active',
            2 => 'suspended',
            default => 'inactive',
        };
    }

    private function resolveKycStatus(): ?string
    {
        $verification = $this->whenLoaded('latestVerification');

        if (!$verification) {
            return null;
        }

        $status = strtolower($verification->status ?? '');

        return match ($status) {
            'verified', 'approved' => 'approved',
            'rejected', 'declined' => 'rejected',
            'pending', '' => 'pending',
            default => null,
        };
    }
}
