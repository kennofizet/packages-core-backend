<?php

namespace Kennofizet\PackagesCore\Traits;

use Kennofizet\PackagesCore\Services\TokenService;

trait HasRewardPlayToken
{
    /**
     * Get RewardPlay token for this user — creates one if it doesn't exist.
     */
    public function getRewardplayToken(): ?string
    {
        $tokenService = app(TokenService::class);
        $token = $tokenService->getToken($this->id);

        if (!$token) {
            $token = $tokenService->createOrRefreshToken($this->id);
        }

        return $token;
    }

    /**
     * Refresh RewardPlay token for this user.
     */
    public function refreshRewardplayToken(): string
    {
        return app(TokenService::class)->createOrRefreshToken($this->id);
    }
}
