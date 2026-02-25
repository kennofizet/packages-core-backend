<?php

namespace Kennofizet\PackagesCore\Traits;

use Kennofizet\PackagesCore\Core\Model\BaseModelResponse;

trait GlobalDataTrait
{
    /**
     * Return a JSON success response
     */
    public function apiResponseWithContext(string $message = 'Success', $data = null, int $status = 200)
    {
        return response()->json(BaseModelResponse::success($message, $data), $status);
    }

    /**
     * Return a JSON error response
     */
    public function apiErrorResponse(string $message = 'Error', int $status = 403, $data = null)
    {
        return response()->json(BaseModelResponse::error($message, $data), $status);
    }

    /**
     * Basic image URL builder (no rewardplay-specific config)
     */
    public function getImageFullUrl(?string $imagePath): string
    {
        if (empty($imagePath)) {
            return '';
        }
        return url($imagePath);
    }
}
