<?php

namespace duncan3dc\Sonos\Cloud;

use duncan3dc\Sonos\Cloud\Exceptions\AuthenticationException;
use duncan3dc\Sonos\Cloud\Interfaces\ApiInterface;
use duncan3dc\Sonos\Cloud\Interfaces\HouseholdInterface;
use duncan3dc\Sonos\Cloud\Interfaces\UserInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\SimpleCache\CacheInterface;
use function serialize;
use function trim;
use function unserialize;

final class User implements UserInterface
{
    /** @var string */
    private $username;

    /** @var ApiInterface */
    private $api;

    /** @var CacheInterface */
    private $cache;

    /** @var string */
    private $cacheKey;

    /**
     * Create a new instance.
     *
     * @param string $username
     * @param ApiInterface $api
     * @param CacheInterface $cache
     */
    public function __construct(string $username, ApiInterface $api, CacheInterface $cache)
    {
        $this->username = trim($username);

        $this->api = $api;
        $this->cache = $cache;

        $this->cacheKey = "access_token_{$this->username}";
    }


    /**
     * @inheritDoc
     */
    public function isAuthorised(): bool
    {
        try {
            $token = $this->getToken();
        } catch (\Throwable $e) {
            return false;
        }

        if ($token->hasExpired()) {
            return false;
        }

        return true;
    }


    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->api->getUrl();
    }


    /**
     * @inheritDoc
     */
    public function setCode(string $code): void
    {
        $token = $this->api->getAccessToken($code);

        $data = serialize($token);

        $this->cache->set($this->cacheKey, $data);
    }


    /**
     * Get the access token for this user.
     *
     * @return AccessToken
     */
    private function getToken(): AccessToken
    {
        if (!$this->cache->has($this->cacheKey)) {
            throw new AuthenticationException("No token, or old token, or invalid token, authorise again");
        }

        $data = $this->cache->get($this->cacheKey);

        return unserialize($data, ["allowed_classes" => [AccessToken::class]]);
    }


    /**
     * @inheritDoc
     */
    public function request(string $method, string $url, array $data = []): \stdClass
    {
        return $this->api->request($method, $url, $data, $this->getToken());
    }


    /**
     * @inheritDoc
     */
    public function getHouseholds(): iterable
    {
        $data = $this->request("GET", "households");
        foreach ($data->households as $household) {
            yield new Household($household->id, $this);
        }
    }


    /**
     * @inheritDoc
     */
    public function getHousehold(string $id): HouseholdInterface
    {
        return new Household($id, $this);
    }
}
