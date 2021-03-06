<?php

namespace duncan3dc\Sonos\CloudTests;

use duncan3dc\Sonos\Cloud\Group;
use duncan3dc\Sonos\Cloud\Interfaces\ClientInterface;
use duncan3dc\Sonos\Cloud\Interfaces\GroupInterface;
use duncan3dc\Sonos\Cloud\Interfaces\HouseholdInterface;
use duncan3dc\Sonos\Cloud\Player;
use duncan3dc\Sonos\Common\Utils\Time;
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


    public function isPlayingProvider()
    {
        $states = [
            "PLAYBACK_STATE_BUFFERING" => false,
            "PLAYBACK_STATE_IDLE" => false,
            "PLAYBACK_STATE_PAUSED" => false,
            "PLAYBACK_STATE_PLAYING" => true,
        ];
        foreach ($states as $state => $playing) {
            yield [$state, $playing];
        }
    }
    /**
     * @dataProvider isPlayingProvider
     */
    public function testIsPlaying(string $state, bool $expected)
    {
        $this->api->shouldReceive("request")->once()->with("GET", "groups/GROUP_1/playback")->andReturn([
            "playbackState" => $state,
        ]);
        $actual = $this->group->isPlaying();
        $this->assertSame($expected, $actual);
    }


    public function testNext1()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/skipToNextTrack");
        $result = $this->group->next();
        $this->assertSame($this->group, $result);
    }


    public function testPrevious1()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/skipToPreviousTrack");
        $result = $this->group->previous();
        $this->assertSame($this->group, $result);
    }


    public function testSeek1()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/seek", [
            "positionMillis" => 65000,
        ]);
        $time = Time::parse("1:05");
        $result = $this->group->seek($time);
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

        $data = [
            "groups" => [
                [
                    "id" => "GROUP_1",
                    "name" => "Bedroom + 1",
                    "coordinatorId" => "PLAYER_1",
                    "playerIds" => ["PLAYER_1", "PLAYER_3"],
                ],
                [
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


    public function testGetRepeat1()
    {
        $this->api->shouldReceive("request")->once()->with("GET", "groups/GROUP_1/playback")->andReturn([
            "playModes" => [
                "repeat" => false,
            ],
        ]);
        $result = $this->group->getRepeat();
        $this->assertSame(false, $result);
    }


    public function testGetRepeat2()
    {
        $this->api->shouldReceive("request")->once()->with("GET", "groups/GROUP_1/playback")->andReturn([
            "playModes" => [
                "repeat" => true,
            ],
        ]);
        $result = $this->group->getRepeat();
        $this->assertSame(true, $result);
    }


    public function testSetRepeat1()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/playMode", [
            "playModes" => [
                "repeat" => true,
                "repeatOne" => false,
            ],
        ]);
        $result = $this->group->setRepeat(true);
        $this->assertSame($this->group, $result);
    }


    public function testSetRepeat2()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/playMode", [
            "playModes" => [
                "repeat" => false,
                "repeatOne" => false,
            ],
        ]);
        $result = $this->group->setRepeat(false);
        $this->assertSame($this->group, $result);
    }


    public function testGetShuffle1()
    {
        $this->api->shouldReceive("request")->once()->with("GET", "groups/GROUP_1/playback")->andReturn([
            "playModes" => [
                "shuffle" => false,
            ],
        ]);
        $result = $this->group->getShuffle();
        $this->assertSame(false, $result);
    }


    public function testGetShuffle2()
    {
        $this->api->shouldReceive("request")->once()->with("GET", "groups/GROUP_1/playback")->andReturn([
            "playModes" => [
                "shuffle" => true,
            ],
        ]);
        $result = $this->group->getShuffle();
        $this->assertSame(true, $result);
    }


    public function testSetShuffle1()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/playMode", [
            "playModes" => [
                "shuffle" => true,
            ],
        ]);
        $result = $this->group->setShuffle(true);
        $this->assertSame($this->group, $result);
    }


    public function testSetShuffle2()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/playMode", [
            "playModes" => [
                "shuffle" => false,
            ],
        ]);
        $result = $this->group->setShuffle(false);
        $this->assertSame($this->group, $result);
    }


    public function testGetCrossfade1()
    {
        $this->api->shouldReceive("request")->once()->with("GET", "groups/GROUP_1/playback")->andReturn([
            "playModes" => [
                "crossfade" => false,
            ],
        ]);
        $result = $this->group->getCrossfade();
        $this->assertSame(false, $result);
    }


    public function testGetCrossfade2()
    {
        $this->api->shouldReceive("request")->once()->with("GET", "groups/GROUP_1/playback")->andReturn([
            "playModes" => [
                "crossfade" => true,
            ],
        ]);
        $result = $this->group->getCrossfade();
        $this->assertSame(true, $result);
    }


    public function testSetCrossfade1()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/playMode", [
            "playModes" => [
                "crossfade" => true,
            ],
        ]);
        $result = $this->group->setCrossfade(true);
        $this->assertSame($this->group, $result);
    }


    public function testSetCrossfade2()
    {
        $this->api->shouldReceive("request")->once()->with("POST", "groups/GROUP_1/playback/playMode", [
            "playModes" => [
                "crossfade" => false,
            ],
        ]);
        $result = $this->group->setCrossfade(false);
        $this->assertSame($this->group, $result);
    }
}
