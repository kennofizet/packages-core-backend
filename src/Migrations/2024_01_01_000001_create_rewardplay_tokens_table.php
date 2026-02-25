<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kennofizet\PackagesCore\Models\Token;

return new class extends Migration {
    public function up()
    {
        $tokensTableName = (new Token())->getTable();

        Schema::create($tokensTableName, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('is_active');
        });
    }

    public function down()
    {
        $tokensTableName = (new Token())->getTable();
        Schema::dropIfExists($tokensTableName);
    }
};
