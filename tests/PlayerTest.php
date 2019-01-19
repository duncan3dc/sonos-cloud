<?php

namespace duncan3dc\Sonos\CloudTests;

use duncan3dc\Sonos\Cloud\Household;
use duncan3dc\Sonos\Cloud\Interfaces\ClientInterface;
use duncan3dc\Sonos\Cloud\Interfaces\HouseholdInterface;
use duncan3dc\Sonos\Cloud\Interfaces\PlayerInterface;
use duncan3dc\Sonos\Cloud\Player;
use duncan3dc\Sonos\Common\Exceptions\NotFoundException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

class PlayerTest extends TestCase
{
    /** @var PlayerInterface */
    private $player;

    /** @var ClientInterface|MockInterface */
    private $api;

    public function setUp()
    {
        $this->api = Mockery::mock(ClientInterface::class);
        $this->player = new Player("RINCON_5CAAFD976D5801400", "Office", $this->api);
    }


    public function tearDown()
    {
        Mockery::close();
    }


    public function testGetId()
    {
        $this->assertSame("RINCON_5CAAFD976D5801400", $this->player->getId());
    }


    public function testGetRoom()
    {
        $this->assertSame("Office", $this->player->getRoom());
    }


    public function testGetVolume()
    {
        $this->api
            ->shouldReceive("request")
            ->once()
            ->with("GET", "players/RINCON_5CAAFD976D5801400/playerVolume")
            ->andReturn([
                "volume" => 70,
            ]);

        $this->assertSame(70, $this->player->getVolume());
    }


    public function testSetVolume()
    {
        $this->api
            ->shouldReceive("request")
            ->once()
            ->with("POST", "players/RINCON_5CAAFD976D5801400/playerVolume", ["volume" => 50]);

        $this->player->setVolume(50);
        $this->assertTrue(true);
    }


    public function testAdjustVolume()
    {
        $this->api
            ->shouldReceive("request")
            ->once()
            ->with("POST", "players/RINCON_5CAAFD976D5801400/playerVolume/relative", ["volumeDelta" => -5]);

        $this->player->adjustVolume(-5);
        $this->assertTrue(true);
    }


    public function testIsMuted1()
    {
        $this->api
            ->shouldReceive("request")
            ->once()
            ->with("GET", "players/RINCON_5CAAFD976D5801400/playerVolume")
            ->andReturn([
                "muted" => 0,
            ]);

        $this->assertFalse($this->player->isMuted());
    }
    public function testIsMuted2()
    {
        $this->api
            ->shouldReceive("request")
            ->once()
            ->with("GET", "players/RINCON_5CAAFD976D5801400/playerVolume")
            ->andReturn([
                "muted" => 1,
            ]);

        $this->assertTrue($this->player->isMuted());
    }


    public function testMute()
    {
        $this->api
            ->shouldReceive("request")
            ->once()
            ->with("POST", "players/RINCON_5CAAFD976D5801400/playerVolume/mute", ["muted" => true]);

        $this->player->mute();
        $this->assertTrue(true);
    }


    public function testUnute()
    {
        $this->api
            ->shouldReceive("request")
            ->once()
            ->with("POST", "players/RINCON_5CAAFD976D5801400/playerVolume/mute", ["muted" => false]);

        $this->player->unmute();
        $this->assertTrue(true);
    }
}
