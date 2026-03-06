<?php

namespace Kennofizet\PackagesCore\Traits;

use Kennofizet\PackagesCore\Services\TokenService;

trait HasKnfCoreToken
{
    /**
     * Get KnfCore token for this user — creates one if it doesn't exist.
     */
    public function getKnfCoreToken(): ?string
    {
        $tokenService = app(TokenService::class);
        $token = $tokenService->getToken($this->id);

        if (!$token) {
            $token = $tokenService->createOrRefreshToken($this->id);
        }

        return $token;
    }

    /**
     * Refresh KnfCore token for this user.
     */
    public function refreshKnfCoreToken(): string
    {
        return app(TokenService::class)->createOrRefreshToken($this->id);
    }
}
