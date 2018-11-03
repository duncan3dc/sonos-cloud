<?php

namespace duncan3dc\Sonos\Cloud\Interfaces;

use League\OAuth2\Client\Token\AccessToken;
use Psr\SimpleCache\CacheInterface;

interface ApiInterface
{
    /**
     * Get the authorisation URL for the API.
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Generate an access token for the API.
     *
     * @param string $code
     *
     * @return AccessToken
     */
    public function getAccessToken(string $code): AccessToken;

    /**
     * Send a request and return the response.
     *
     * @param string $method The HTTP verb to use for the request
     * @param string $url The url to issue the request to (https://api.ws.sonos.com/control/api/v1/ is optional)
     * @param array $data The parameters to send with the request
     * @param AccessToken $token The access token for authorisation
     *
     * @return \stdClass
     */
    public function request(string $method, string $url, array $data, AccessToken $token): \stdClass;

    /**
     * Get a user instance.
     *
     * @param string $username
     * @param CacheInterface|null $cache
     *
     * @return UserInterface
     */
    public function getUser(string $username, CacheInterface $cache = null): UserInterface;
}
