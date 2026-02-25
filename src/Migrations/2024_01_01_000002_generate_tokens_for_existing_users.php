<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Kennofizet\PackagesCore\Models\Token;
use Kennofizet\PackagesCore\Models\User;

return new class extends Migration {
    protected function getTokensTableName()
    {
        return (new Token())->getTable();
    }

    protected function getUserTableName()
    {
        return (new User())->getTable();
    }

    protected function generateUniqueToken($tokensTableName)
    {
        do {
            $token = bin2hex(random_bytes(32));
            $exists = DB::table($tokensTableName)->where('token', $token)->exists();
        } while ($exists);
        return $token;
    }

    public function up(): void
    {
        $tokensTableName = $this->getTokensTableName();
        $userTableName = $this->getUserTableName();

        if (!DB::getSchemaBuilder()->hasTable($tokensTableName)) {
            throw new \Exception("Table '{$tokensTableName}' does not exist.");
        }

        $users = DB::table($userTableName)->get();
        if ($users->isEmpty()) {
            return;
        }

        $tokensToInsert = [];
        $chunkSize = 100;

        foreach ($users as $user) {
            $existingToken = DB::table($tokensTableName)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if (!$existingToken) {
                $tokensToInsert[] = [
                    'user_id' => $user->id,
                    'token' => $this->generateUniqueToken($tokensTableName),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($tokensToInsert) >= $chunkSize) {
                    DB::table($tokensTableName)->insert($tokensToInsert);
                    $tokensToInsert = [];
                }
            }
        }

        if (!empty($tokensToInsert)) {
            DB::table($tokensTableName)->insert($tokensToInsert);
        }
    }

    public function down(): void
    {
        // Preserve existing tokens
    }
};
