<?php

namespace Kennofizet\PackagesCore\Traits\SettingRewardPlay;

use Kennofizet\PackagesCore\Services\ServerManagerService;

trait ServerManagerTrait
{
    public function getServerManagers($filters = [])
    {
        return app(ServerManagerService::class)->getServerManagers($filters);
    }

    public function getServerManagersByServer(?int $serverId = null)
    {
        return app(ServerManagerService::class)->getByServer($serverId);
    }

    public function createServerManager(array $data)
    {
        return app(ServerManagerService::class)->assignManager($data);
    }

    public function deleteServerManager(array $data)
    {
        return app(ServerManagerService::class)->removeManager($data);
    }
}
