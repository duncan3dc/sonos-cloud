<?php

namespace duncan3dc\Sonos\Cloud\Interfaces;

/**
 * Represents an individual Sonos speaker.
 */
interface PlayerInterface
{
    /**
     * Get the ID of this speaker.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get the room name of this speaker.
     *
     * @return string
     */
    public function getRoom(): string;
}
