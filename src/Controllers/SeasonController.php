<?php

namespace Kennofizet\PackagesCore\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;
use Kennofizet\PackagesCore\Services\SeasonService;
use Kennofizet\PackagesCore\Traits\GlobalDataTrait;

class SeasonController
{
    use GlobalDataTrait, BaseModelActions;

    public function __construct(
        private readonly SeasonService $seasonService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $seasons = $this->seasonService->listByCurrentZone();
        return $this->apiResponseWithContext('Success', [
            'seasons' => $seasons,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            return $this->apiErrorResponse('name is required', 422);
        }

        try {
            $season = $this->seasonService->createForCurrentZone(
                $name,
                $request->input('starts_at'),
                $request->input('ends_at')
            );
        } catch (\Throwable $e) {
            return $this->apiErrorResponse($e->getMessage(), 422);
        }

        return $this->apiResponseWithContext('Success', [
            'season' => [
                'id' => (int) $season->id,
                'name' => (string) $season->name,
                'is_active' => (bool) $season->is_active,
                'starts_at' => $season->starts_at?->toIso8601String(),
                'ends_at' => $season->ends_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function activate(Request $request, int $seasonId): JsonResponse
    {
        try {
            $season = $this->seasonService->activateForCurrentZone($seasonId);
        } catch (\Throwable $e) {
            return $this->apiErrorResponse($e->getMessage(), 422);
        }

        return $this->apiResponseWithContext('Success', [
            'season' => [
                'id' => (int) $season->id,
                'name' => (string) $season->name,
                'is_active' => (bool) $season->is_active,
            ],
        ]);
    }
}
