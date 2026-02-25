<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add deleted_at (soft deletes) to ALL model tables that extend BaseModel.
 * BaseModel is defined in packages-core and uses SoftDeletes by default.
 *
 * This migration belongs in packages-core because the soft-delete behaviour
 * (the deleted_at column) is a feature of the core BaseModel, not of any
 * specific package.
 *
 * Covers:
 *   - Core models  : Zone, Token, ServerManager  (Kennofizet\PackagesCore\*)
 *   - RewardPlay models : SettingEvent, SettingShopItem, UserProfile, etc.  (Kennofizet\RewardPlay\*)
 *
 * Excluded:
 *   - User  (extends vanilla Model, no SoftDeletes)
 *   - ZoneUser  (extends Pivot, no SoftDeletes)
 */
return new class extends Migration {
    protected function allBaseModelClasses(): array
    {
        return [
            // ── Core models ────────────────────────────────────────────────
            \Kennofizet\PackagesCore\Models\Zone::class,
            \Kennofizet\PackagesCore\Models\Token::class,
            \Kennofizet\PackagesCore\Models\ServerManager::class,

            // ── RewardPlay models ──────────────────────────────────────────
            \Kennofizet\RewardPlay\Models\SettingEvent::class,
            \Kennofizet\RewardPlay\Models\SettingShopItem::class,
            \Kennofizet\RewardPlay\Models\UserProfile::class,
            \Kennofizet\RewardPlay\Models\UserRankingSnapshot::class,
            \Kennofizet\RewardPlay\Models\UserEventTransaction::class,
            \Kennofizet\RewardPlay\Models\SettingStatsTransform::class,
            \Kennofizet\RewardPlay\Models\SettingLevelExp::class,
            \Kennofizet\RewardPlay\Models\UserBagItem::class,
            \Kennofizet\RewardPlay\Models\SettingItemSet::class,
            \Kennofizet\RewardPlay\Models\SettingItem::class,
            \Kennofizet\RewardPlay\Models\UserDailyStatus::class,
            \Kennofizet\RewardPlay\Models\SettingStackBonus::class,
            \Kennofizet\RewardPlay\Models\SettingDailyReward::class,
            \Kennofizet\RewardPlay\Models\SettingOption::class,
            \Kennofizet\RewardPlay\Models\SettingItemSetItem::class,
            \Kennofizet\RewardPlay\Models\ZoneManager::class,
        ];
    }

    public function up(): void
    {
        foreach ($this->allBaseModelClasses() as $class) {
            if (!class_exists($class)) {
                continue;
            }

            try {
                $model = new $class();
                $table = $model->getTable();
            } catch (\Throwable $e) {
                continue;
            }

            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->allBaseModelClasses() as $class) {
            if (!class_exists($class)) {
                continue;
            }

            try {
                $model = new $class();
                $table = $model->getTable();
            } catch (\Throwable $e) {
                continue;
            }

            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropSoftDeletes();
            });
        }
    }
};
