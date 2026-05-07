<?php

namespace Kennofizet\PackagesCore\Events;

use Kennofizet\PackagesCore\Models\Season;

class SeasonCreated
{
    public function __construct(
        public readonly Season $season
    ) {
    }
}
