<?php

namespace duncan3dc\Sonos\Cloud\Interfaces;

use duncan3dc\Sonos\Common\Interfaces\PlayerInterface as CommonInterface;

/**
 * Represents an individual Sonos speaker.
 */
interface PlayerInterface extends CommonInterface
{
    /**
     * Get the ID of this speaker.
     *
     * @return string
     */
    public function getId(): string;
}
