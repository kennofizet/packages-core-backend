<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kennofizet\PackagesCore\Models\ServerManager;
use Kennofizet\PackagesCore\Models\User;

return new class extends Migration {
    public function up()
    {
        $serverManagersTableName = (new ServerManager())->getTable();
        $userTable = (new User())->getTable();

        Schema::create($serverManagersTableName, function (Blueprint $table) use ($userTable) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('server_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on($userTable)->onDelete('cascade');

            $table->unique(['user_id', 'server_id']);
            $table->index('user_id');
            $table->index('server_id');
        });
    }

    public function down()
    {
        $serverManagersTableName = (new ServerManager())->getTable();
        Schema::dropIfExists($serverManagersTableName);
    }
};
