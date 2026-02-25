<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kennofizet\PackagesCore\Models\ServerManager;

return new class extends Migration {
    public function up(): void
    {
        $tableName = (new ServerManager())->getTable();

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('server_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $tableName = (new ServerManager())->getTable();

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('server_id')->nullable(false)->change();
            });
        }
    }
};
