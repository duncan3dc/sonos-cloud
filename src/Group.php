<?php

namespace duncan3dc\Sonos\Cloud;

use duncan3dc\Sonos\Cloud\Interfaces\ClientInterface;
use duncan3dc\Sonos\Cloud\Interfaces\GroupInterface;
use duncan3dc\Sonos\Cloud\Interfaces\HouseholdInterface;
use duncan3dc\Sonos\Cloud\Interfaces\PlayerInterface;

/**
 * Allows interaction with the groups of speakers.
 */
final class Group implements GroupInterface
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var HouseholdInterface */
    private $household;

    /** @var ClientInterface */
    private $api;

    /**
     * Create an instance of the Speaker class.
     *
     * @param string $id The ID of the speaker
     * @param string $name The name of the group
     * @param HouseholdInterface $household The household this group is part of
     * @param ClientInterface $api
     */
    public function __construct(string $id, string $name, HouseholdInterface $household, ClientInterface $api)
    {
        $this->id = $id;
        $this->name = $name;
        $this->household = $household;
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
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @inheritDoc
     */
    public function play(): GroupInterface
    {
        $this->api->request("POST", "groups/{$this->id}/playback/play");
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function pause(): GroupInterface
    {
        $this->api->request("POST", "groups/{$this->id}/playback/pause");
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getPlayers(): iterable
    {
        $household = $this->household->getId();
        $data = $this->api->request("GET", "households/{$household}/groups");

        $players = $this->household->getPlayers();

        foreach ($data["groups"] as $group) {
            if ($group["id"] !== $this->id) {
                continue;
            }

            foreach ($group["playerIds"] as $id) {
                foreach ($players as $player) {
                    if ($player->getId() === $id) {
                        yield $player;
                        break;
                    }
                }
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function addPlayer(PlayerInterface $player): GroupInterface
    {
        $this->api->request("POST", "groups/{$this->id}/groups/modifyGroupMembers", [
            "playerIdsToAdd" => [$player->getId()],
        ]);

        $this->household->reload();

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function removePlayer(PlayerInterface $player): GroupInterface
    {
        $this->api->request("POST", "groups/{$this->id}/groups/modifyGroupMembers", [
            "playerIdsToRemove" => [$player->getId()],
        ]);

        $this->household->reload();

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getRepeat(): bool
    {
        $status = $this->api->request("GET", "groups/{$this->id}/playback");

        return $status["playModes"]["repeat"];
    }


    /**
     * @inheritDoc
     */
    public function setRepeat(bool $repeat): GroupInterface
    {
        $this->api->request("POST", "groups/{$this->id}/playback/playMode", [
            "playModes" => [
                "repeat" => $repeat,
                "repeatOne" => false,
            ],
        ]);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getShuffle(): bool
    {
        $status = $this->api->request("GET", "groups/{$this->id}/playback");

        return $status["playModes"]["shuffle"];
    }


    /**
     * @inheritDoc
     */
    public function setShuffle(bool $shuffle): GroupInterface
    {
        $this->api->request("POST", "groups/{$this->id}/playback/playMode", [
            "playModes" => [
                "shuffle" => $shuffle,
            ],
        ]);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getCrossfade(): bool
    {
        $status = $this->api->request("GET", "groups/{$this->id}/playback");

        return $status["playModes"]["crossfade"];
    }


    /**
     * @inheritDoc
     */
    public function setCrossfade(bool $crossfade): GroupInterface
    {
        $this->api->request("POST", "groups/{$this->id}/playback/playMode", [
            "playModes" => [
                "crossfade" => $crossfade,
            ],
        ]);

        return $this;
    }
}
