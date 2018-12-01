<?php

namespace duncan3dc\Sonos\Cloud\Interfaces;

use duncan3dc\Sonos\Common\Interfaces\GroupInterface as CommonInterface;

/**
 * Allows interaction with the groups of speakers.
 */
interface GroupInterface extends CommonInterface
{
    /**
     * Get the ID of this group.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get the name of this group.
     *
     * @return string
     */
    public function getName(): string;
}
