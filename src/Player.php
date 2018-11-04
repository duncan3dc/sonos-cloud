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
}
