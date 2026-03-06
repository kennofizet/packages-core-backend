<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// ZoneManager was removed; this migration safely drops it if it exists
return new class extends Migration {
    protected function getZoneManagerTableName(): string
    {
        $prefix = config('packages-core.table_prefix', '');
        return $prefix . 'knf_core_zone_managers';
    }

    public function up()
    {
        Schema::dropIfExists($this->getZoneManagerTableName());
    }

    public function down()
    {
        // Not re-creating the old zone_manager table
    }
};
