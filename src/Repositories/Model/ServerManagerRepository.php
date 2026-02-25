<?php

namespace Kennofizet\PackagesCore\Repositories\Model;

use Kennofizet\PackagesCore\Models\ServerManager;

class ServerManagerRepository
{
    public function create(array $data): ServerManager
    {
        return ServerManager::create([
            'user_id' => $data['user_id'],
            'server_id' => $data['server_id'],
        ]);
    }

    public function update(ServerManager $serverManager, array $data): ServerManager
    {
        $serverManager->update([
            'user_id' => $data['user_id'],
            'server_id' => $data['server_id'],
        ]);
        return $serverManager;
    }

    public function remove(?int $serverId, int $userId): bool
    {
        return (bool) ServerManager::byServer($serverId)
            ->byUser($userId)
            ->delete();
    }
}
