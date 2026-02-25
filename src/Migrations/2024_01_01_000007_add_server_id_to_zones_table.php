<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kennofizet\PackagesCore\Models\Zone;

return new class extends Migration {
    public function up()
    {
        $zonesTableName = (new Zone())->getTable();

        Schema::table($zonesTableName, function (Blueprint $table) use ($zonesTableName) {
            if (!Schema::hasColumn($zonesTableName, 'server_id')) {
                $table->unsignedBigInteger('server_id')->nullable()->after('name');
                $table->index('server_id');
            }
        });
    }

    public function down()
    {
        $zonesTableName = (new Zone())->getTable();

        Schema::table($zonesTableName, function (Blueprint $table) use ($zonesTableName) {
            if (Schema::hasColumn($zonesTableName, 'server_id')) {
                $table->dropIndex(['server_id']);
                $table->dropColumn('server_id');
            }
        });
    }
};
