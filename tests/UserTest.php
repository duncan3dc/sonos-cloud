<?php

namespace duncan3dc\Sonos\CloudTests;

use duncan3dc\Cache\ArrayPool;
use duncan3dc\Sonos\Cloud\Interfaces\ApiInterface;
use duncan3dc\Sonos\Cloud\Interfaces\GroupInterface;
use duncan3dc\Sonos\Cloud\Interfaces\HouseholdInterface;
use duncan3dc\Sonos\Cloud\Interfaces\PlayerInterface;
use duncan3dc\Sonos\Cloud\Interfaces\UserInterface;
use duncan3dc\Sonos\Cloud\User;
use duncan3dc\Sonos\Common\Exceptions\NotFoundException;
use League\OAuth2\Client\Token\AccessToken;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use function iterator_to_array;
use function serialize;

class UserTest extends TestCase
{
    /** @var UserInterface */
    private $user;

    /** @var ApiInterface|MockInterface */
    private $api;

    /** @var CacheInterface */
    private $cache;


    public function setUp()
    {
        $this->api = Mockery::mock(ApiInterface::class);
        $this->cache = new ArrayPool();
        $this->user = new User("USER_1", $this->api, $this->cache);
    }


    public function tearDown()
    {
        Mockery::close();
    }


    private function mockAuthorisation(): void
    {
        $token = new AccessToken([
            "access_token" => "example-token-value",
            "expires" => time() + 600,
        ]);
        $this->api->shouldReceive("getAccessToken")->once()->with("example-token-value")->andReturn($token);

        $this->user->setCode("example-token-value");
    }


    public function testIsAuthorised1()
    {
        $result = $this->user->isAuthorised();
        $this->assertSame(false, $result);
    }


    public function testIsAuthorised2()
    {
        $token = new AccessToken([
            "access_token" => "blahblah",
            "expires" => time() - 60,
        ]);
        $this->cache->set("access_token_USER_1", serialize($token));

        $result = $this->user->isAuthorised();
        $this->assertSame(false, $result);
    }


    public function testIsAuthorised3()
    {
        $token = new AccessToken([
            "access_token" => "blahblah",
            "expires" => time() + 60,
        ]);
        $this->cache->set("access_token_USER_1", serialize($token));

        $result = $this->user->isAuthorised();
        $this->assertSame(true, $result);
    }


    public function testGetUrl1()
    {
        $this->api->shouldReceive("getUrl")->once()->with()->andReturn("https://authorise.me/");

        $result = $this->user->getUrl();
        $this->assertSame("https://authorise.me/", $result);
    }


    public function testSetCode1()
    {
        $this->assertSame(false, $this->user->isAuthorised());

        $token = new AccessToken([
            "access_token" => "token",
            "expires" => time() + 60,
        ]);
        $this->api->shouldReceive("getAccessToken")->once()->with("token")->andReturn($token);
        $this->user->setCode("token");

        $this->assertSame(true, $this->user->isAuthorised());
    }


    public function testRequest1()
    {
        $this->mockAuthorisation();

        $this->api->shouldReceive("request")->once()
            ->with("GET", "/thing", ["data" => "stuff"], \Mockery::type(AccessToken::class))
            ->andReturn(["result" => 475]);

        $result = $this->user->request("GET", "/thing", ["data" => "stuff"]);
        $this->assertSame(["result" => 475], $result);
    }


    public function testGetHouseholds1()
    {
        $this->mockAuthorisation();

        $this->api->shouldReceive("request")->once()
            ->with("GET", "households", [], \Mockery::type(AccessToken::class))
            ->andReturn([
                "households" => [
                    ["id" => "HOUSEHOLD_1"],
                    ["id" => "HOUSEHOLD_2"],
                ],
            ]);

        $households = $this->user->getHouseholds();
        $households = is_array($households) ? $households : iterator_to_array($households);
        $this->assertContainsOnlyInstancesOf(HouseholdInterface::class, $households);

        $this->assertSame("HOUSEHOLD_1", $households[0]->getId());
        $this->assertSame("HOUSEHOLD_2", $households[1]->getId());
    }


    public function testGetHousehold1()
    {
        $household = $this->user->getHousehold("HOUSEHOLD_3");
        $this->assertSame("HOUSEHOLD_3", $household->getId());
    }
}
