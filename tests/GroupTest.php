<?php

namespace duncan3dc\Sonos\CloudTests;

use duncan3dc\Sonos\Cloud\Group;
use duncan3dc\Sonos\Cloud\Interfaces\ClientInterface;
use duncan3dc\Sonos\Cloud\Interfaces\GroupInterface;
use duncan3dc\Sonos\Cloud\Interfaces\HouseholdInterface;
use duncan3dc\Sonos\Cloud\Player;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    /** @var GroupInterface */
    private $group;

    /** @var HouseholdInterface|MockInterface */
    private $household;

    /** @var ClientInterface|MockInterface */
    private $api;

    public function setUp()
    {
        $this->api = Mockery::mock(ClientInterface::class);
        $this->household = Mockery::mock(HouseholdInterface::class);
        $this->group = new Group("GROUP_1", "Bedroom + 3", $this->household, $this->api);
    }


    public function tearDown()
    {
        Mockery::close();
    }


    public function testGetId()
    {
        $id = $this->group->getId();
        $this->assertSame("GROUP_1", $id);
    }


    public function testGetName()
    {
        $id = $this->group->getName();
        $this->assertSame("Bedroom + 3", $id);
    }


    public function testPlay()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/play");
        $result = $this->group->play();
        $this->assertSame($this->group, $result);
    }


    public function testPause()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/pause");
        $result = $this->group->pause();
        $this->assertSame($this->group, $result);
    }


    public function testGetPlayers()
    {
        $this->household->shouldReceive("getId")->once()->with()->andReturn("HOUSEHOLD_1");
        $this->household->shouldReceive("getPlayers")->once()->with()->andReturn([
            new Player("PLAYER_1", "Bedroom", $this->api),
            new Player("PLAYER_2", "Office", $this->api),
            new Player("PLAYER_3", "Spare Room", $this->api),
        ]);

        $data = (object) [
            "groups" => [
                (object) [
                    "id" => "GROUP_1",
                    "name" => "Bedroom + 1",
                    "coordinatorId" => "PLAYER_1",
                    "playerIds" => ["PLAYER_1", "PLAYER_3"],
                ],
                (object) [
                    "id" => "GROUP_2",
                    "name" => "Office",
                    "coordinatorId" => "PLAYER_2",
                    "playerIds" => ["PLAYER_2"],
                ],
            ],
        ];
        $this->api->shouldReceive("request")->once()->with("GET", "households/HOUSEHOLD_1/groups")->andReturn($data);

        $players = [];
        foreach ($this->group->getPlayers() as $player) {
            $players[] = $player->getId();
        }
        $this->assertSame(["PLAYER_1", "PLAYER_3"], $players);
    }


    public function testAddPlayer()
    {
        $player = new Player("PLAYER_4", "Bedroom", $this->api);

        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/groups/modifyGroupMembers", [
            "playerIdsToAdd" => ["PLAYER_4"],
        ]);

        $this->household->shouldReceive("reload")->once()->with();

        $result = $this->group->addPlayer($player);
        $this->assertSame($this->group, $result);
    }


    public function testRemovePlayer()
    {
        $player = new Player("PLAYER_4", "Bedroom", $this->api);

        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/groups/modifyGroupMembers", [
            "playerIdsToRemove" => ["PLAYER_4"],
        ]);

        $this->household->shouldReceive("reload")->once()->with();

        $result = $this->group->removePlayer($player);
        $this->assertSame($this->group, $result);
    }
}
