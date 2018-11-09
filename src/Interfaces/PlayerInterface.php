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

    /**
     * Get the current volume of this speaker.
     *
     * @return int The current volume between 0 and 100
     */
    public function getVolume(): int;

    /**
     * Adjust the volume of this speaker to a specific value.
     *
     * @param int $volume The amount to set the volume to between 0 and 100
     *
     * @return void
     */
    public function setVolume(int $volume): void;

    /**
     * Adjust the volume of this speaker by a relative amount.
     *
     * @param int $adjust The amount to adjust by between -100 and 100
     *
     * @return void
     */
    public function adjustVolume(int $adjust): void;

    /**
     * Check if this speaker is currently muted.
     *
     * @return bool
     */
    public function isMuted(): bool;

    /**
     * Mute this speaker.
     *
     * @return void
     */
    public function mute(): void;

    /**
     * Unmute this speaker.
     *
     * @return void
     */
    public function unmute(): void;
}
