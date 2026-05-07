<?php

namespace Kennofizet\PackagesCore\Contracts;

use Kennofizet\PackagesCore\Models\Season;

interface AfterSeasonCreatedListener
{
    public function handle(Season $season): void;
}
