<?php

namespace Kennofizet\PackagesCore\Traits;

use Kennofizet\PackagesCore\Traits\Settings\ZoneTrait;
use Kennofizet\PackagesCore\Traits\Settings\ServerManagerTrait;

/**
 * Facade trait exposing zone and server manager helpers.
 */
trait SettingKnfCore
{
    use ZoneTrait, ServerManagerTrait;
}
