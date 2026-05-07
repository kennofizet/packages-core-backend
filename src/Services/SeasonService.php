<?php

namespace Kennofizet\PackagesCore\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Kennofizet\PackagesCore\Contracts\AfterSeasonCreatedListener;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;
use Kennofizet\PackagesCore\Events\SeasonCreated;
use Kennofizet\PackagesCore\Models\Season;

class SeasonService
{
    /**
     * @return array<int, array{id:int, name:string, is_active:bool, starts_at:?string, ends_at:?string}>
     */
    public function listByCurrentZone(): array
    {
        if (!$this->seasonTableExists()) {
            return [];
        }
        $zoneId = BaseModelActions::currentUserZoneId();
        if (empty($zoneId)) {
            return [];
        }

        return Season::withoutGlobalScopes()
            ->where('zone_id', $zoneId)
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get()
            ->map(static function (Season $season): array {
                return [
                    'id' => (int) $season->id,
                    'name' => (string) $season->name,
                    'is_active' => (bool) $season->is_active,
                    'starts_at' => $season->starts_at?->toIso8601String(),
                    'ends_at' => $season->ends_at?->toIso8601String(),
                ];
            })->values()->all();
    }

    public function getActiveSeasonIdForZone(int $zoneId): ?int
    {
        if (!$this->seasonTableExists()) {
            return null;
        }
        $season = Season::withoutGlobalScopes()
            ->where('zone_id', $zoneId)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        return $season ? (int) $season->id : null;
    }

    public function createForCurrentZone(string $name, ?string $startsAt = null, ?string $endsAt = null): Season
    {
        if (!$this->seasonTableExists()) {
            throw new \RuntimeException('Season table is missing');
        }
        $zoneId = BaseModelActions::currentUserZoneId();
        if (empty($zoneId)) {
            throw new \RuntimeException('Current zone is required');
        }

        $season = DB::transaction(function () use ($zoneId, $name, $startsAt, $endsAt) {
            Season::withoutGlobalScopes()
                ->where('zone_id', $zoneId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            return Season::withoutGlobalScopes()->create([
                'zone_id' => $zoneId,
                'name' => $name,
                'is_active' => true,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);
        });

        $eventClass = config('packages-core.season_event_class', SeasonCreated::class);
        event(new $eventClass($season));
        $this->runAfterSeasonCreatedListeners($season);

        return $season;
    }

    public function activateForCurrentZone(int $seasonId): Season
    {
        if (!$this->seasonTableExists()) {
            throw new \RuntimeException('Season table is missing');
        }
        $zoneId = BaseModelActions::currentUserZoneId();
        if (empty($zoneId)) {
            throw new \RuntimeException('Current zone is required');
        }

        return DB::transaction(function () use ($zoneId, $seasonId) {
            $season = Season::withoutGlobalScopes()
                ->where('zone_id', $zoneId)
                ->where('id', $seasonId)
                ->first();

            if (!$season) {
                throw new \RuntimeException('Season not found');
            }

            Season::withoutGlobalScopes()
                ->where('zone_id', $zoneId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $season->is_active = true;
            $season->save();

            return $season;
        });
    }

    private function runAfterSeasonCreatedListeners(Season $season): void
    {
        $listeners = config('packages-core.after_season_created_listeners', []);
        if (!is_array($listeners)) {
            return;
        }

        foreach ($listeners as $class) {
            if (!is_string($class) || !class_exists($class)) {
                continue;
            }

            $listener = app()->make($class);
            if ($listener instanceof AfterSeasonCreatedListener) {
                $listener->handle($season);
            }
        }
    }

    private function seasonTableExists(): bool
    {
        return Schema::hasTable((new Season())->getTable());
    }
}
