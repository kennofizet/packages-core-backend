<?php

namespace Kennofizet\PackagesCore\Services;

use Kennofizet\PackagesCore\Repositories\Model\ZoneRepository;
use Kennofizet\PackagesCore\Services\Validation\ZoneValidationService;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;
use Kennofizet\PackagesCore\Models\Zone;
use Kennofizet\PackagesCore\Models\User;
use Illuminate\Validation\ValidationException;

class ZoneService
{
    protected ZoneRepository $zoneRepository;
    protected ZoneValidationService $validation;

    public function __construct(
        ZoneRepository $zoneRepository,
        ZoneValidationService $validation
    ) {
        $this->zoneRepository = $zoneRepository;
        $this->validation = $validation;
    }

    /**
     * Get zones list.
     */
    public function getZones($filters = [])
    {
        $query = Zone::query();

        if (!empty($filters['name'])) {
            $query->search($filters['name']);
        }

        return $query->get();
    }

    /**
     * Create zone.
     *
     * @throws ValidationException
     * @throws \Exception
     */
    public function createZone(array $data): Zone
    {
        $this->validation->validateZone($data);

        return $this->zoneRepository->create($data);
    }

    /**
     * Edit zone.
     *
     * @throws ValidationException
     * @throws \Exception
     */
    public function editZone(int $zoneId, array $data): ?Zone
    {
        $this->validation->validateZone($data);

        $zone = Zone::findById($zoneId);
        if (!$zone) {
            return null;
        }

        $managedZoneIds = BaseModelActions::currentUserManagedZoneIds();
        if (!in_array($zoneId, $managedZoneIds)) {
            throw new \Exception('You do not have permission to manage this zone');
        }

        return $this->zoneRepository->update($zone, $data);
    }

    /**
     * Delete zone.
     *
     * @throws \Exception
     */
    public function deleteZone(int $zoneId): bool
    {
        $zone = Zone::findById($zoneId);
        if (!$zone) {
            return false;
        }

        $managedZoneIds = BaseModelActions::currentUserManagedZoneIds();
        if (!in_array($zoneId, $managedZoneIds)) {
            throw new \Exception('You do not have permission to manage this zone');
        }

        return $this->zoneRepository->delete($zone);
    }

    /**
     * Get zones that current user can manage.
     */
    public function getZonesUserCanManage(): array
    {
        $zoneIds = BaseModelActions::currentUserManagedZoneIds();
        if (empty($zoneIds)) {
            return [];
        }

        $zones = Zone::byZoneIds($zoneIds)->get();

        return $zones->map(fn($zone) => [
            'id' => $zone->id,
            'name' => $zone->name,
        ])->toArray();
    }

    /**
     * Get users that belong to the current server.
     */
    public function getServerUsers(array $filters = [])
    {
        $query = User::query()->byServer();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->get();
    }

    /**
     * Get users assigned to a zone.
     */
    public function getZoneUsers(int $zoneId): array
    {
        $zone = Zone::findById($zoneId);
        if (!$zone) {
            return [];
        }

        return $zone->users()->get()->toArray();
    }

    /**
     * Assign a user to a zone.
     *
     * @throws \Exception
     */
    public function assignUserToZone(int $zoneId, int $userId): bool
    {
        $zone = Zone::findById($zoneId);
        if (!$zone) {
            throw new \Exception('Zone not found');
        }

        $managedZoneIds = BaseModelActions::currentUserManagedZoneIds();
        if (!in_array($zoneId, $managedZoneIds)) {
            throw new \Exception('You do not have permission to manage this zone');
        }

        $user = User::query()->byServer()->find($userId);
        if (!$user) {
            throw new \Exception('User not found in this server');
        }

        $zone->users()->syncWithoutDetaching([$userId]);

        return true;
    }

    /**
     * Remove a user from a zone.
     *
     * @throws \Exception
     */
    public function removeUserFromZone(int $zoneId, int $userId): bool
    {
        $zone = Zone::findById($zoneId);
        if (!$zone) {
            throw new \Exception('Zone not found');
        }

        $managedZoneIds = BaseModelActions::currentUserManagedZoneIds();
        if (!in_array($zoneId, $managedZoneIds)) {
            throw new \Exception('You do not have permission to manage this zone');
        }

        $zone->users()->detach([$userId]);

        return true;
    }
}
