<?php

namespace duncan3dc\Sonos\CloudTests;

use duncan3dc\Sonos\Cloud\Household;
use duncan3dc\Sonos\Cloud\Interfaces\ClientInterface;
use duncan3dc\Sonos\Cloud\Interfaces\GroupInterface;
use duncan3dc\Sonos\Cloud\Interfaces\HouseholdInterface;
use duncan3dc\Sonos\Cloud\Interfaces\PlayerInterface;
use duncan3dc\Sonos\Common\Exceptions\NotFoundException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

class HouseholdTest extends TestCase
{
    /** @var HouseholdInterface */
    private $household;

    /** @var ClientInterface|MockInterface */
    private $api;

    public function setUp()
    {
        $this->api = Mockery::mock(ClientInterface::class);
        $this->household = new Household("HOUSEHOLD_1", $this->api);
    }


    public function tearDown()
    {
        Mockery::close();
    }


    public function testGetId()
    {
        $id = $this->household->getId();
        $this->assertSame("HOUSEHOLD_1", $id);
    }


    public function testReload()
    {
        $data = (object) [
            "players" => [
                (object) ["id" => "PLAYER_1", "name" => "Bedroom"],
            ],
        ];
        $this->api->shouldReceive("request")->twice()->with("GET", "households/HOUSEHOLD_1/groups")->andReturn($data);

        $players = $this->household->getPlayers();
        $this->assertContainsOnlyInstancesOf(PlayerInterface::class, $players);

        # Ensure calling it again doesn't hit the API
        $this->household->getPlayers();

        # This should trigger a second API request
        $this->household->reload();
        $players = $this->household->getPlayers();
        $this->assertContainsOnlyInstancesOf(PlayerInterface::class, $players);
    }


    public function testGetPlayers()
    {
        $data = (object) [
            "players" => [
                (object) ["id" => "PLAYER_1", "name" => "Bedroom"],
            ],
        ];
        $this->api->shouldReceive("request")->once()->with("GET", "households/HOUSEHOLD_1/groups")->andReturn($data);

        $players = $this->household->getPlayers();

        $this->assertContainsOnlyInstancesOf(PlayerInterface::class, $players);

        # Ensure calling it again doesn't hit the API
        $this->household->getPlayers();
    }


    public function testGetPlayersByRoom()
    {
        $data = (object) [
            "players" => [
                (object) ["id" => "PLAYER_1", "name" => "Bedroom"],
                (object) ["id" => "PLAYER_2", "name" => "Office"],
                (object) ["id" => "PLAYER_3", "name" => "Bedroom"],
            ],
        ];
        $this->api->shouldReceive("request")->once()->with("GET", "households/HOUSEHOLD_1/groups")->andReturn($data);

        $players = $this->household->getPlayersByRoom("Bedroom");
        $players = is_array($players) ? $players : iterator_to_array($players);

        $this->assertContainsOnlyInstancesOf(PlayerInterface::class, $players);

        $result = array_map(function (PlayerInterface $player) {
            return $player->getId();
        }, $players);
        $this->assertSame(["PLAYER_1", "PLAYER_3"], $result);

        # Ensure calling it again doesn't hit the API
        $this->household->getPlayersByRoom("Office");
    }


    public function testGetPlayerByRoom1()
    {
        $data = (object) [
            "players" => [
                (object) ["id" => "PLAYER_1", "name" => "Bedroom"],
                (object) ["id" => "PLAYER_2", "name" => "Office"],
            ],
        ];
        $this->api->shouldReceive("request")->once()->with("GET", "households/HOUSEHOLD_1/groups")->andReturn($data);

        $player = $this->household->getPlayerByRoom("Office");
        $this->assertSame("PLAYER_2", $player->getId());

        # Ensure calling it again doesn't hit the API
        $this->household->getPlayerByRoom("Bedroom");
    }
    public function testGetPlayerByRoom2()
    {
        $data = (object) [
            "players" => [
                (object) ["id" => "PLAYER_1", "name" => "Bedroom"],
                (object) ["id" => "PLAYER_2", "name" => "Office"],
            ],
        ];
        $this->api->shouldReceive("request")->once()->with("GET", "households/HOUSEHOLD_1/groups")->andReturn($data);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find a player for the room 'Nope'");
        $this->household->getPlayerByRoom("Nope");
    }


    public function testGetGroups()
    {
        $data = (object) [
            "players" => [
                (object) ["id" => "PLAYER_1", "name" => "Bedroom"],
                (object) ["id" => "PLAYER_2", "name" => "Office"],
                (object) ["id" => "PLAYER_3", "name" => "Spare Room"],
            ],
            "groups" => [
                (object) [
                    "id" => "GROUP_1",
                    "name" => "Bedroom + 1",
                    "coordinatorId" => "PLAYER_1",
                    "playbackState" => "PLAYBACK_STATE_IDLE",
                    "playerIds" => ["PLAYER_1", "PLAYER_3"],
                ],
                (object) [
                    "id" => "GROUP_2",
                    "name" => "Office",
                    "coordinatorId" => "PLAYER_2",
                    "playbackState" => "PLAYBACK_STATE_IDLE",
                    "playerIds" => ["PLAYER_2"],
                ],
            ],
        ];
        $this->api->shouldReceive("request")->once()->with("GET", "households/HOUSEHOLD_1/groups")->andReturn($data);

        $groups = $this->household->getGroups();

        $this->assertContainsOnlyInstancesOf(GroupInterface::class, $groups);

        # Ensure calling it again doesn't hit the API
        $this->household->getGroups();
    }


    public function testGetGroupByRoom1()
    {
        $data = (object) [
            "players" => [
                (object) ["id" => "PLAYER_1", "name" => "Bedroom"],
                (object) ["id" => "PLAYER_2", "name" => "Office"],
            ],
            "groups" => [
                (object) [
                    "id" => "GROUP_1",
                    "name" => "Bedroom + 1",
                    "coordinatorId" => "PLAYER_1",
                    "playbackState" => "PLAYBACK_STATE_IDLE",
                    "playerIds" => ["PLAYER_1", "PLAYER_2"],
                ],
            ],
        ];
        $this->api->shouldReceive("request")->once()->with("GET", "households/HOUSEHOLD_1/groups")->andReturn($data);

        $group = $this->household->getGroupByRoom("Office");
        $this->assertSame("GROUP_1", $group->getId());

        # Ensure calling it again doesn't hit the API
        $this->household->getGroupByRoom("Office");
    }
    public function testGetGroupByRoom2()
    {
        $data = (object) [
            "players" => [
                (object) ["id" => "PLAYER_1", "name" => "Bedroom"],
                (object) ["id" => "PLAYER_2", "name" => "Office"],
            ],
            "groups" => [
                (object) [
                    "id" => "GROUP_1",
                    "name" => "Bedroom",
                    "coordinatorId" => "PLAYER_1",
                    "playbackState" => "PLAYBACK_STATE_IDLE",
                    "playerIds" => ["PLAYER_1"],
                ],
            ],
        ];
        $this->api->shouldReceive("request")->once()->with("GET", "households/HOUSEHOLD_1/groups")->andReturn($data);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find a group for the room 'Office'");
        $this->household->getGroupByRoom("Office");
    }
}
