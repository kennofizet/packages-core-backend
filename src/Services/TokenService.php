<?php

namespace Kennofizet\PackagesCore\Services;

use Kennofizet\PackagesCore\Models\Token;
use Kennofizet\PackagesCore\Models\User;

class TokenService
{
    public function createOrRefreshToken($userId): string
    {
        $token = $this->generateUniqueToken();
        $existingToken = Token::byUser($userId)->active()->first();

        if ($existingToken) {
            $existingToken->update(['token' => $token]);
        } else {
            Token::create([
                'user_id' => $userId,
                'token' => $token,
                'is_active' => true,
            ]);
        }

        return $token;
    }

    public function getToken($userId): ?string
    {
        $token = Token::byUser($userId)->active()->first();
        return $token ? $token->token : null;
    }

    protected function generateUniqueToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
            $exists = Token::byToken($token)->exists();
        } while ($exists);

        return $token;
    }

    public function validateToken($token): ?int
    {
        $tokenRecord = Token::byToken($token)->active()->first();
        return $tokenRecord ? $tokenRecord->user_id : null;
    }

    public function checkUser($token): ?array
    {
        $tokenRecord = Token::byToken($token)->active()->first();
        if (!$tokenRecord) {
            return null;
        }

        $user = User::byId($tokenRecord->user_id)->first();
        if (!$user) {
            return null;
        }

        return [
            'token_active' => $tokenRecord->is_active ? 1 : 0,
        ];
    }
}
