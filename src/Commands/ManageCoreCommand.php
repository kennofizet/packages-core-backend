<?php

namespace Kennofizet\PackagesCore\Commands;

use Illuminate\Console\Command;
use Kennofizet\PackagesCore\Models\Zone;
use Kennofizet\PackagesCore\Models\ServerManager;
use Kennofizet\PackagesCore\Traits\GlobalDataTrait;

/**
 * ManageCoreCommand — full CRUD for Zones and ServerManagers
 *
 * This command handles Create / Edit / Delete operations.
 * Use 'rewardplay:manage' for read-only listing within rewardplay context.
 */
class ManageCoreCommand extends Command
{
    use GlobalDataTrait;

    protected $signature = 'packages-core:manage';
    protected $description = 'Manage core infrastructure: create/edit/delete Zones and Server Managers';

    protected $currentServerId = null;
    protected $currentZoneId = null;

    public function handle()
    {
        $this->info('=== PackagesCore Management Console ===');
        $this->newLine();

        if (!$this->hasServerIdConfig()) {
            $this->line('Server ID column not configured. Zones with server_id = null will be used.');
            $this->newLine();
        }

        while (true) {
            $this->displayMainMenu();
            $options = [
                'Select Server',
                'Manage Zones (Create / Edit / Delete)',
                'Manage Server Managers (Add / Remove)',
                'Show Current Selection',
                'Exit',
            ];

            $choice = $this->choice('Select an option', $options, count($options) - 1);

            switch ($choice) {
                case 'Select Server':
                    $this->selectServer();
                    break;
                case 'Manage Zones (Create / Edit / Delete)':
                    $this->manageZones();
                    break;
                case 'Manage Server Managers (Add / Remove)':
                    $this->manageServerManagers();
                    break;
                case 'Show Current Selection':
                    $this->showCurrentSelection();
                    break;
                case 'Exit':
                    $this->info('Goodbye!');
                    return Command::SUCCESS;
            }
            $this->newLine();
        }
    }

    protected function hasServerIdConfig(): bool
    {
        return !empty(config('packages-core.user_server_id_column'));
    }

    protected function displayMainMenu()
    {
        $this->info('--- Main Menu ---');
        $this->line('Current Server ID: ' . ($this->currentServerId ?? 'Not selected'));
        if ($this->currentZoneId) {
            $zone = Zone::findById($this->currentZoneId);
            $this->line('Current Zone: ' . ($zone ? $zone->name : 'ID ' . $this->currentZoneId));
        } else {
            $this->line('Current Zone: Not selected');
        }
        $this->newLine();
    }

    // ── Server Selection ──────────────────────────────────────────────────────

    protected function selectServer()
    {
        $this->info('--- Select Server ---');

        if (!$this->hasServerIdConfig()) {
            $this->currentServerId = null;
            $this->line('No server column configured. Using server_id = null.');
            return;
        }

        $serverIds = Zone::withoutGlobalScopes()
            ->distinct()->whereNotNull('server_id')->pluck('server_id')->toArray();

        if (empty($serverIds)) {
            $this->warn('No servers found. Enter manually:');
            $manual = $this->ask('Server ID (or Enter to skip)');
            if ($manual) {
                $this->currentServerId = (int) $manual;
            }
            return;
        }

        $options = [];
        foreach ($serverIds as $id) {
            $count = Zone::withoutGlobalScopes()->byServerId($id)->count();
            $options[] = "Server ID: {$id} ({$count} zone(s))";
        }
        $options[] = 'Cancel';

        $selected = $this->choice('Select a server', $options, count($options) - 1);
        if ($selected === 'Cancel')
            return;

        $idx = array_search($selected, $options);
        $this->currentServerId = $serverIds[$idx];
        $this->currentZoneId = null;
        $this->info('Selected Server ID: ' . $this->currentServerId);
    }

    // ── Zone Management ───────────────────────────────────────────────────────

    protected function manageZones()
    {
        if ($this->hasServerIdConfig() && !$this->currentServerId) {
            $this->error('Please select a server first!');
            return;
        }

        while (true) {
            $this->info('--- Zone Management (Server: ' . ($this->currentServerId ?? 'null') . ') ---');
            $choice = $this->choice('Select an option', [
                'List Zones',
                'Create Zone',
                'Select Zone',
                'Edit Zone',
                'Delete Zone',
                'Back',
            ], 5);

            switch ($choice) {
                case 'List Zones':
                    $this->listZones();
                    break;
                case 'Create Zone':
                    $this->createZone();
                    break;
                case 'Select Zone':
                    $this->selectZone();
                    break;
                case 'Edit Zone':
                    $this->editZone();
                    break;
                case 'Delete Zone':
                    $this->deleteZone();
                    break;
                case 'Back':
                    return;
            }
            $this->newLine();
        }
    }

    protected function listZones()
    {
        try {
            $zones = Zone::withoutGlobalScopes()->byServerId($this->currentServerId)->get();
            if ($zones->isEmpty()) {
                $this->warn('No zones found.');
                return;
            }
            $this->table(
                ['ID', 'Name', 'Server ID', 'Created At'],
                $zones->map(fn($z) => [$z->id, $z->name, $z->server_id ?? 'N/A', optional($z->created_at)->format('Y-m-d H:i:s') ?? 'N/A'])->toArray()
            );
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    protected function createZone()
    {
        $name = $this->ask('Zone name');
        if (empty($name)) {
            $this->error('Name required!');
            return;
        }

        try {
            $zone = Zone::create(['name' => $name, 'server_id' => $this->currentServerId]);
            $this->info('✓ Zone created — ID: ' . $zone->id . ', Name: ' . $zone->name);
        } catch (\Exception $e) {
            $this->error('✗ ' . $e->getMessage());
        }
    }

    protected function selectZone()
    {
        try {
            $zones = Zone::withoutGlobalScopes()->byServerId($this->currentServerId)->get();
            if ($zones->isEmpty()) {
                $this->warn('No zones found.');
                return;
            }

            $options = $zones->map(fn($z) => $z->name . ' (ID: ' . $z->id . ')')->toArray();
            $options[] = 'Cancel';
            $selected = $this->choice('Select zone', $options, count($options) - 1);
            if ($selected === 'Cancel')
                return;

            $idx = array_search($selected, $options);
            $this->currentZoneId = $zones[$idx]->id;
            $this->info('Selected: ' . $zones[$idx]->name);
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    protected function editZone()
    {
        if (!$this->currentZoneId) {
            $this->error('Select a zone first!');
            return;
        }
        $zone = Zone::withoutGlobalScopes()->find($this->currentZoneId);
        if (!$zone) {
            $this->error('Zone not found!');
            return;
        }

        $newName = $this->ask('New name', $zone->name);
        try {
            $zone->update(['name' => $newName ?: $zone->name]);
            $this->info('✓ Zone updated: ' . $zone->fresh()->name);
        } catch (\Exception $e) {
            $this->error('✗ ' . $e->getMessage());
        }
    }

    protected function deleteZone()
    {
        if (!$this->currentZoneId) {
            $this->error('Select a zone first!');
            return;
        }
        $zone = Zone::withoutGlobalScopes()->find($this->currentZoneId);
        if (!$zone) {
            $this->error('Zone not found!');
            return;
        }

        $this->line('Zone: ' . $zone->name . ' (ID: ' . $zone->id . ')');
        if (!$this->confirm('Delete this zone?', false)) {
            $this->info('Cancelled.');
            return;
        }

        try {
            $zone->delete();
            $this->currentZoneId = null;
            $this->info('✓ Zone deleted.');
        } catch (\Exception $e) {
            $this->error('✗ ' . $e->getMessage());
        }
    }

    // ── Server Manager Management ─────────────────────────────────────────────

    protected function manageServerManagers()
    {
        if ($this->hasServerIdConfig() && !$this->currentServerId) {
            $this->error('Please select a server first!');
            return;
        }

        while (true) {
            $this->info('--- Server Manager Management (Server: ' . ($this->currentServerId ?? 'null') . ') ---');
            $choice = $this->choice('Select an option', [
                'List Managers',
                'Add Manager',
                'Remove Manager',
                'Back',
            ], 3);

            switch ($choice) {
                case 'List Managers':
                    $this->listServerManagers();
                    break;
                case 'Add Manager':
                    $this->addServerManager();
                    break;
                case 'Remove Manager':
                    $this->removeServerManager();
                    break;
                case 'Back':
                    return;
            }
            $this->newLine();
        }
    }

    protected function listServerManagers()
    {
        try {
            $managers = ServerManager::withoutGlobalScopes()->byServer($this->currentServerId)->get();
            if ($managers->isEmpty()) {
                $this->warn('No managers found.');
                return;
            }
            $this->table(
                ['ID', 'User ID', 'Server ID', 'Created At'],
                $managers->map(fn($m) => [$m->id, $m->user_id, $m->server_id ?? 'null', optional($m->created_at)->format('Y-m-d H:i:s') ?? 'N/A'])->toArray()
            );
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    protected function addServerManager()
    {
        $userId = $this->ask('User ID');
        if (!is_numeric($userId)) {
            $this->error('Valid user ID required!');
            return;
        }

        try {
            $manager = ServerManager::create(['user_id' => (int) $userId, 'server_id' => $this->currentServerId]);
            $this->info('✓ Manager added — ID: ' . $manager->id . ', User: ' . $manager->user_id);
        } catch (\Exception $e) {
            $this->error('✗ ' . $e->getMessage());
        }
    }

    protected function removeServerManager()
    {
        try {
            $managers = ServerManager::withoutGlobalScopes()->byServer($this->currentServerId)->get();
            if ($managers->isEmpty()) {
                $this->warn('No managers found.');
                return;
            }

            $options = $managers->map(fn($m) => 'User ID: ' . $m->user_id . ' (ID: ' . $m->id . ')')->toArray();
            $options[] = 'Cancel';
            $selected = $this->choice('Select manager to remove', $options, count($options) - 1);
            if ($selected === 'Cancel')
                return;

            $idx = array_search($selected, $options);
            $manager = $managers[$idx];

            if (!$this->confirm('Remove manager (User ID: ' . $manager->user_id . ')?', false)) {
                $this->info('Cancelled.');
                return;
            }

            $manager->delete();
            $this->info('✓ Manager removed.');
        } catch (\Exception $e) {
            $this->error('✗ ' . $e->getMessage());
        }
    }

    protected function showCurrentSelection()
    {
        $this->info('--- Current Selection ---');
        $this->line('Server ID: ' . ($this->currentServerId ?? 'null'));
        $zones = Zone::withoutGlobalScopes()->byServerId($this->currentServerId)->count();
        $managers = ServerManager::withoutGlobalScopes()->byServer($this->currentServerId)->count();
        $this->line("Zones: {$zones} | Managers: {$managers}");
        if ($this->currentZoneId) {
            $zone = Zone::withoutGlobalScopes()->find($this->currentZoneId);
            $this->line('Selected Zone: ' . ($zone ? $zone->name : 'ID ' . $this->currentZoneId));
        }
    }
}
