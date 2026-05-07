<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kennofizet\PackagesCore\Models\Season;

return new class extends Migration {
    public function up()
    {
        $tableName = (new Season())->getTable();
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['zone_id', 'is_active'], 'knf_core_season_zone_active_idx');
            $table->index(['zone_id', 'created_at'], 'knf_core_season_zone_created_idx');
            $table->unique(['zone_id', 'name'], 'knf_core_season_zone_name_unique');
        });
    }

    public function down()
    {
        $tableName = (new Season())->getTable();
        Schema::dropIfExists($tableName);
    }
};
