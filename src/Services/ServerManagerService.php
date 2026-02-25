<?php

namespace Kennofizet\PackagesCore\Services;

use Kennofizet\PackagesCore\Repositories\Model\ServerManagerRepository;
use Kennofizet\PackagesCore\Services\Validation\ServerManagerValidationService;
use Kennofizet\PackagesCore\Models\ServerManager;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;
use Illuminate\Validation\ValidationException;

class ServerManagerService
{
    protected ServerManagerRepository $repository;
    protected ServerManagerValidationService $validation;

    public function __construct(
        ServerManagerRepository $repository,
        ServerManagerValidationService $validation
    ) {
        $this->repository = $repository;
        $this->validation = $validation;
    }

    /**
     * Get server managers list.
     */
    public function getServerManagers($filters = [])
    {
        $query = ServerManager::query();

        $managedServerId = BaseModelActions::currentUserManagedServerId();
        if ($managedServerId === null && !BaseModelActions::canManageServer(null)) {
            return collect([]);
        }

        if (array_key_exists('server_id', $filters)) {
            $query->byServer($filters['server_id'] ?? null);
        } else {
            $query->byServer($managedServerId);
        }

        if (!empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        return $query->get();
    }

    /**
     * Get managers by server.
     */
    public function getByServer(?int $serverId = null)
    {
        return ServerManager::findByServerId($serverId);
    }

    /**
     * Assign manager to server.
     *
     * @throws ValidationException
     */
    public function assignManager(array $data): ServerManager
    {
        $this->validation->validateAssign($data);

        $existingManager = ServerManager::byUser($data['user_id'])
            ->byServer($data['server_id'])
            ->first();

        if ($existingManager) {
            return $existingManager;
        }

        return $this->repository->create($data);
    }

    /**
     * Remove manager from server.
     *
     * @throws ValidationException
     */
    public function removeManager(array $data): bool
    {
        $this->validation->validateAssign($data);

        return $this->repository->remove($data['server_id'], $data['user_id']);
    }
}
