<?php

namespace duncan3dc\Sonos\Cloud;

use duncan3dc\Cache\ArrayPool;
use duncan3dc\Sonos\Cloud\Interfaces\ClientInterface;
use duncan3dc\Sonos\Cloud\Interfaces\HouseholdInterface;
use duncan3dc\Sonos\Common\Exceptions\NotFoundException;
use duncan3dc\Sonos\Common\Interfaces\GroupInterface;
use duncan3dc\Sonos\Common\Interfaces\PlayerInterface;
use Psr\SimpleCache\CacheInterface;

final class Household implements HouseholdInterface
{
    /** @var string */
    private $id;

    /** @var ClientInterface */
    private $api;

    /** @var CacheInterface */
    private $cache;

    /**
     * Create a new instance.
     *
     * @param string $id
     * @param ClientInterface $api
     * @param CacheInterface|null $cache
     */
    public function __construct(string $id, ClientInterface $api, CacheInterface $cache = null)
    {
        $this->id = $id;
        $this->api = $api;

        if ($cache === null) {
            $cache = new ArrayPool();
        }
        $this->cache = $cache;
    }


    /**
     * Get the data for this household.
     *
     * @return \stdClass
     */
    private function getData(): \stdClass
    {
        if (!$this->cache->has("data")) {
            $data = $this->api->request("GET", "households/{$this->id}/groups");
            $this->cache->set("data", $data);
        }

        return $this->cache->get("data");
    }


    /**
     * @inheritDoc
     */
    public function reload(): HouseholdInterface
    {
        $this->cache->delete("data");
        return $this;
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
    public function getPlayers(): iterable
    {
        foreach ($this->getData()->players as $data) {
            yield new Player($data->id, $data->name, $this->api);
        }
    }


    /**
     * @inheritDoc
     */
    public function getPlayersByRoom(string $room): iterable
    {
        foreach ($this->getPlayers() as $player) {
            if ($player->getRoom() === $room) {
                yield $player;
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function getPlayerByRoom(string $room): PlayerInterface
    {
        foreach ($this->getPlayersByRoom($room) as $player) {
            return $player;
        }

        throw new NotFoundException("Unable to find a player for the room '{$room}'");
    }


    /**
     * @inheritDoc
     */
    public function getGroups(): iterable
    {
        foreach ($this->getData()->groups as $data) {
            yield new Group($data->id, $data->name, $this, $this->api);
        }
    }


    /**
     * @inheritDoc
     */
    public function getGroupByRoom(string $room): GroupInterface
    {
        $player = $this->getPlayerByRoom($room);

        foreach ($this->getData()->groups as $data) {
            foreach ($data->playerIds as $id) {
                if ($player->getId() === $id) {
                    return new Group($data->id, $data->name, $this, $this->api);
                }
            }
        }

        throw new NotFoundException("Unable to find a group for the room '{$room}'");
    }
}
