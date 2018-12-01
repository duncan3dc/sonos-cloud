<?php

namespace duncan3dc\Sonos\Cloud\Interfaces;

use duncan3dc\Sonos\Common\Interfaces\HouseholdInterface as CommonInterface;

/**
 * Provides methods to locate players/groups on the household.
 */
interface HouseholdInterface extends CommonInterface
{
    /**
     * Reload all the device information for this household.
     *
     * @return $this
     */
    public function reload(): self;

    /**
     * Get the ID of this household.
     *
     * @return string
     */
    public function getId(): string;
}
