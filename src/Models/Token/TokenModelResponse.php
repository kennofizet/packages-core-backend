<?php

namespace Kennofizet\PackagesCore\Models\Token;

use Kennofizet\PackagesCore\Core\Model\BaseModelResponse;
use Kennofizet\PackagesCore\Models\Token\TokenConstant;
use Kennofizet\PackagesCore\Helpers\Constant as HelperConstant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TokenModelResponse extends BaseModelResponse
{
    public static function getAvailableModeDefault(): string
    {
        return TokenConstant::API_TOKEN_LIST_PAGE;
    }

    public static function formatToken($token, $mode = ''): array
    {
        if (!$token) {
            return [];
        }

        if (in_array($mode, [self::getAvailableModeDefault()])) {
            return [
                'id' => $token->id,
                'user_id' => $token->user_id,
                'is_active' => $token->is_active,
            ];
        } elseif (in_array($mode, [HelperConstant::REPONSE_MODE_SELECTER_API])) {
            return [
                'id' => $token->id,
                'user_id' => $token->user_id,
                'is_active' => $token->is_active,
            ];
        }

        return [
            'id' => $token->id,
            'user_id' => $token->user_id,
            'is_active' => $token->is_active,
        ];
    }

    public static function formatTokens($tokens, ?string $mode = null): array
    {
        $mode = $mode ?? self::getAvailableModeDefault();

        if ($tokens instanceof LengthAwarePaginator) {
            return [
                'data' => $tokens->map(fn($t) => self::formatToken($t, $mode)),
                'current_page' => $tokens->currentPage(),
                'total' => $tokens->total(),
                'last_page' => $tokens->lastPage(),
            ];
        }

        if ($tokens instanceof Collection) {
            return $tokens->map(fn($t) => self::formatToken($t, $mode))->toArray();
        }

        return [];
    }
}
