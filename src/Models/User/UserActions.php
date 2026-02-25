<?php

namespace Kennofizet\PackagesCore\Models\User;

use Kennofizet\PackagesCore\Models\User;
use Kennofizet\PackagesCore\Models\Zone;
use Kennofizet\PackagesCore\Models\ServerManager;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;

trait UserActions
{
    /**
     * Find user by ID
     */
    public static function findById(int $id): ?User
    {
        return User::byId($id)->first();
    }

    // ──────────────────────────────────────────────────────────────
    // Zone / Server Manager helpers (core)
    // ──────────────────────────────────────────────────────────────

    /**
     * Get this user's active zone IDs.
     */
    public function getUserZoneIds(): array
    {
        return $this->zones()->pluck('zones.id')->toArray();
    }

    /**
     * Get the zone manager record for a given zone.
     */
    public function getZoneManager(int $zoneId): ?ServerManager
    {
        return ServerManager::where('user_id', $this->id)
            ->where('zone_id', $zoneId)
            ->first();
    }

    /**
     * Get all server managers for this user.
     */
    public function getServerManagers()
    {
        return ServerManager::where('user_id', $this->id)->get();
    }

    /**
     * Create a zone manager record.
     */
    public function createZoneManager(array $data): ServerManager
    {
        return ServerManager::create(array_merge(['user_id' => $this->id], $data));
    }

    /**
     * Delete a zone manager record.
     */
    public function deleteZoneManager(array $data): bool
    {
        return (bool) ServerManager::where('user_id', $data['user_id'] ?? $this->id)
            ->where('zone_id', $data['zone_id'])
            ->delete();
    }

    // ──────────────────────────────────────────────────────────────
    // RewardPlay-specific actions (guarded — only run when rewardplay is loaded)
    // ──────────────────────────────────────────────────────────────

    /**
     * Give an item to the user.
     */
    public function giveItem(array $data)
    {
        $class = 'Kennofizet\RewardPlay\Models\UserBagItem';
        if (!class_exists($class)) {
            throw new \RuntimeException('RewardPlay package not loaded.');
        }
        return $class::createBagItem(array_merge(['user_id' => $this->id], $data));
    }

    /**
     * Get user profile (creates if not exists).
     */
    protected function getOrCreateProfile()
    {
        $class = 'Kennofizet\RewardPlay\Models\UserProfile';
        if (!class_exists($class)) {
            throw new \RuntimeException('RewardPlay package not loaded.');
        }
        return $class::getOrCreateProfile($this->id);
    }

    public function getCoin(): int
    {
        return $this->getOrCreateProfile()->coin ?? 0;
    }

    public function getRuby(): int
    {
        return $this->getOrCreateProfile()->ruby ?? 0;
    }

    public function getBoxCoin(): int
    {
        return 0;
    }

    public function getLevel(): int
    {
        return $this->getOrCreateProfile()->lv ?? 1;
    }

    public function getExp(): int
    {
        return $this->getOrCreateProfile()->current_exp ?? 0;
    }

    public function getExpNeed(): int
    {
        $profile = $this->getOrCreateProfile();
        $class = 'Kennofizet\RewardPlay\Models\SettingLevelExp';
        if (!class_exists($class)) {
            return 0;
        }
        return $class::getExpForLevel($profile->lv ?? 1);
    }

    public function giveExp(int $amount)
    {
        return $this->getOrCreateProfile()->giveExp($amount);
    }

    public function giveCoin(int $amount)
    {
        return $this->getOrCreateProfile()->giveCoin($amount);
    }

    public function giveRuby(int $amount)
    {
        return $this->getOrCreateProfile()->giveRuby($amount);
    }

    public function deductCoin(int $amount)
    {
        return $this->getOrCreateProfile()->deductCoin($amount);
    }

    public function deductRuby(int $amount)
    {
        return $this->getOrCreateProfile()->deductRuby($amount);
    }

    public function getGears(): array
    {
        return $this->getOrCreateProfile()->gears ?? [];
    }

    public function saveGears(array $gears)
    {
        $profile = $this->getOrCreateProfile();
        $profile->gears = $gears;
        $profile->save();
        return $profile->fresh();
    }

    public function getPower(): int
    {
        $stats = $this->getStats();
        $key = defined('Kennofizet\RewardPlay\Helpers\Constant::POWER_KEY')
            ? \Kennofizet\RewardPlay\Helpers\Constant::POWER_KEY
            : 'power';
        return isset($stats[$key]) ? (int) $stats[$key] : 0;
    }

    public function getStats(): array
    {
        $gears = $this->getGears();
        $totalStats = [];

        foreach ($gears as $slot => $gear) {
            if (!is_array($gear) || !isset($gear['properties'])) {
                continue;
            }
            $properties = $gear['properties'];

            if (isset($properties['stats']) && is_array($properties['stats'])) {
                foreach ($properties['stats'] as $k => $v) {
                    $totalStats[$k] = ($totalStats[$k] ?? 0) + (int) $v;
                }
            }

            if (isset($properties['custom_options'])) {
                $opts = $properties['custom_options'];
                $opts = is_array($opts) && isset($opts[0]) ? $opts : [$opts];
                foreach ($opts as $opt) {
                    if (is_array($opt) && isset($opt['properties']) && is_array($opt['properties'])) {
                        foreach ($opt['properties'] as $k => $v) {
                            $totalStats[$k] = ($totalStats[$k] ?? 0) + (int) $v;
                        }
                    }
                }
            }
        }

        $svcClass = 'Kennofizet\RewardPlay\Services\Model\SettingStatsTransformService';
        if (class_exists($svcClass)) {
            $transforms = app($svcClass)->getActiveTransforms();
            foreach ($transforms as $transform) {
                $targetKey = $transform['target_key'];
                $conversions = $transform['conversions'] ?? [];
                foreach ($conversions as $conversion) {
                    $sourceKey = $conversion['source_key'] ?? null;
                    $convVal = (float) ($conversion['conversion_value'] ?? 0);
                    if ($sourceKey && isset($totalStats[$sourceKey])) {
                        $totalStats[$targetKey] = ($totalStats[$targetKey] ?? 0)
                            + (float) $totalStats[$sourceKey] * $convVal;
                    }
                }
            }
        }

        return $totalStats;
    }

    public function getCurrentSets(): array
    {
        $setItemClass = 'Kennofizet\RewardPlay\Models\SettingItemSetItem';
        $setClass = 'Kennofizet\RewardPlay\Models\SettingItemSet';
        if (!class_exists($setItemClass) || !class_exists($setClass)) {
            return [];
        }

        $gears = $this->getGears();
        $wornItemIds = [];
        foreach ($gears as $gear) {
            if (is_array($gear) && isset($gear['item_id'])) {
                $wornItemIds[] = (int) $gear['item_id'];
            }
        }
        if (empty($wornItemIds)) {
            return [];
        }

        $setIds = $setItemClass::whereIn('item_id', $wornItemIds)->distinct()->pluck('set_id')->toArray();
        if (empty($setIds)) {
            return [];
        }

        $currentSets = [];
        $sets = $setClass::whereIn('id', $setIds)->with('items')->get();
        foreach ($sets as $set) {
            $setItemIds = $set->items->pluck('id')->toArray();
            $itemCount = count(array_intersect($wornItemIds, $setItemIds));
            if ($itemCount === 0) {
                continue;
            }
            $totalItems = $set->items->count();
            $currentBonuses = [];
            foreach ($set->set_bonuses ?? [] as $level => $bonus) {
                if ($level === 'full') {
                    if ($itemCount >= $totalItems) {
                        $currentBonuses['full'] = $bonus;
                    }
                } elseif ((int) $level <= $itemCount) {
                    $currentBonuses[$level] = $bonus;
                }
            }
            $currentSets[] = [
                'set_id' => $set->id,
                'set_name' => $set->name ?? null,
                'item_count' => $itemCount,
                'total_items' => $totalItems,
                'current_bonus' => $currentBonuses,
            ];
        }
        return $currentSets;
    }

    public function getGearsSets(): array
    {
        $profileConstClass = 'Kennofizet\RewardPlay\Models\UserProfile\UserProfileConstant';
        $setItemClass = 'Kennofizet\RewardPlay\Models\SettingItemSetItem';
        if (!class_exists($profileConstClass) || !class_exists($setItemClass)) {
            return [];
        }

        $gears = $this->getGears();
        $currentSets = $this->getCurrentSets();
        $setIdToIndex = [];
        foreach ($currentSets as $i => $set) {
            $setIdToIndex[$set['set_id']] = $i;
        }

        $allSlots = $profileConstClass::getAllGearSlots();
        $gearsSets = [];
        foreach ($allSlots as $slot) {
            $slotKey = $slot['key'];
            $indices = [];
            if (isset($gears[$slotKey]) && is_array($gears[$slotKey]) && isset($gears[$slotKey]['item_id'])) {
                $itemId = (int) $gears[$slotKey]['item_id'];
                $setIds = $setItemClass::where('item_id', $itemId)->distinct()->pluck('set_id')->toArray();
                foreach ($setIds as $sid) {
                    if (isset($setIdToIndex[$sid])) {
                        $indices[] = $setIdToIndex[$sid];
                    }
                }
            }
            $indices = array_values(array_unique($indices));
            sort($indices);
            $gearsSets[$slotKey] = $indices;
        }
        return $gearsSets;
    }

    public function hasTransaction(array $data)
    {
        $class = 'Kennofizet\RewardPlay\Models\UserEventTransaction';
        if (!class_exists($class)) {
            throw new \RuntimeException('RewardPlay package not loaded.');
        }
        return $class::createTransaction(array_merge(['user_id' => $this->id], $data));
    }
}
