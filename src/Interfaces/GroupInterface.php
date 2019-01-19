<?php

namespace duncan3dc\Sonos\Cloud\Interfaces;

use duncan3dc\Sonos\Common\Interfaces\Utils\TimeInterface;

/**
 * Allows interaction with the groups of speakers.
 */
interface GroupInterface
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

    /**
     * Start playing the active music for this group.
     *
     * @return $this
     */
    public function play(): self;

    /**
     * Pause the group.
     *
     * @return $this
     */
    public function pause(): self;

    /**
     * Check if this group is playing music or not.
     *
     * @return bool
     */
    public function isPlaying(): bool;

    /**
     * Skip to the next track in the current queue.
     *
     * @return $this
     */
    public function next(): self;

    /**
     * Skip back to the previous track in the current queue.
     *
     * @return $this
     */
    public function previous(): self;

    /**
     * Seeks to a specific position within the current track.
     *
     * @param TimeInterface $position The position to seek to in the track
     *
     * @return $this
     */
    public function seek(TimeInterface $position): self;

    /**
     * Get the speakers that are in this group.
     *
     * @return iterable|PlayerInterface[]
     */
    public function getPlayers(): iterable;

    /**
     * Adds the specified player to this group.
     *
     * @param PlayerInterface $player The speaker to add to the group
     *
     * @return $this
     */
    public function addPlayer(PlayerInterface $player): self;

    /**
     * Removes the specified speaker from this group.
     *
     * @param PlayerInterface $player The speaker to remove from the group
     *
     * @return $this
     */
    public function removePlayer(PlayerInterface $player): self;

    /**
     * Check if repeat is currently active.
     *
     * @return bool
     */
    public function getRepeat(): bool;

    /**
     * Turn repeat mode on or off.
     *
     * @param bool $repeat Whether repeat should be on or not
     *
     * @return $this
     */
    public function setRepeat(bool $repeat): self;

    /**
     * Check if shuffle is currently active.
     *
     * @return bool
     */
    public function getShuffle(): bool;

    /**
     * Turn shuffle mode on or off.
     *
     * @param bool $shuffle Whether shuffle should be on or not
     *
     * @return $this
     */
    public function setShuffle(bool $shuffle): self;

    /**
     * Check if crossfade is currently active.
     *
     * @return bool
     */
    public function getCrossfade(): bool;

    /**
     * Turn crossfade on or off.
     *
     * @param bool $crossfade Whether crossfade should be on or not
     *
     * @return $this
     */
    public function setCrossfade(bool $crossfade): self;
}
