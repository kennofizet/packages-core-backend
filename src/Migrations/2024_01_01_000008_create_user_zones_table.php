<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kennofizet\PackagesCore\Models\ZoneUser;
use Kennofizet\PackagesCore\Models\Zone;
use Kennofizet\PackagesCore\Models\User;

return new class extends Migration {
    public function up()
    {
        $zoneUsersTableName = (new ZoneUser())->getTable();
        $userTable = (new User())->getTable();
        $zonesTableName = (new Zone())->getTable();

        Schema::create($zoneUsersTableName, function (Blueprint $table) use ($userTable, $zonesTableName) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('zone_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on($userTable)->onDelete('cascade');
            $table->foreign('zone_id')->references('id')->on($zonesTableName)->onDelete('cascade');

            $table->unique(['user_id', 'zone_id']);
            $table->index('user_id');
            $table->index('zone_id');
        });
    }

    public function down()
    {
        $zoneUsersTableName = (new ZoneUser())->getTable();
        Schema::dropIfExists($zoneUsersTableName);
    }
};
