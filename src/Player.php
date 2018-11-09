<?php

namespace duncan3dc\Sonos\Cloud;

use duncan3dc\Sonos\Cloud\Interfaces\ClientInterface;
use duncan3dc\Sonos\Cloud\Interfaces\PlayerInterface;

/**
 * Represents an individual Sonos speaker.
 */
final class Player implements PlayerInterface
{
    /** @var string */
    private $id;

    /** @var string */
    private $room;

    /** @var ClientInterface */
    private $api;

    /**
     * Create an instance of the Speaker class.
     *
     * @param string $id The ID of the speaker
     * @param string $room The room name the speaker is in
     * @param ClientInterface $api
     */
    public function __construct(string $id, string $room, ClientInterface $api)
    {
        $this->id = $id;
        $this->room = $room;
        $this->api = $api;
    }


    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * @inheritDoc
     */
    public function getRoom(): string
    {
        return $this->room;
    }


    /**
     * Get the current volume of this speaker.
     *
     * @return int The current volume between 0 and 100
     */
    public function getVolume(): int
    {
        return (int) $this->api->request("GET", "players/{$this->id}/playerVolume")->volume;
    }


    /**
     * Adjust the volume of this speaker to a specific value.
     *
     * @param int $volume The amount to set the volume to between 0 and 100
     *
     * @return void
     */
    public function setVolume(int $volume): void
    {
        $this->api->request("POST", "players/{$this->id}/playerVolume", [
            "volume" => $volume,
        ]);
    }


    /**
     * Adjust the volume of this speaker by a relative amount.
     *
     * @param int $adjust The amount to adjust by between -100 and 100
     *
     * @return void
     */
    public function adjustVolume(int $adjust): void
    {
        $this->api->request("POST", "players/{$this->id}/playerVolume/relative", [
            "volumeDelta" => $adjust,
        ]);
    }


    /**
     * Check if this speaker is currently muted.
     *
     * @return bool
     */
    public function isMuted(): bool
    {
        return (bool) $this->api->request("GET", "players/{$this->id}/playerVolume")->muted;
    }


    /**
     * Mute this speaker.
     *
     * @return void
     */
    public function mute(): void
    {
        $this->api->request("POST", "players/{$this->id}/playerVolume/mute", [
            "muted" => true,
        ]);
    }


    /**
     * Unmute this speaker.
     *
     * @return void
     */
    public function unmute(): void
    {
        $this->api->request("POST", "players/{$this->id}/playerVolume/mute", [
            "muted" => false,
        ]);
    }
}
