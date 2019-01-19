<?php

namespace duncan3dc\Sonos\Cloud;

use duncan3dc\Cache\FilesystemPool;
use duncan3dc\Sonos\Cloud\Exceptions\ApiException;
use duncan3dc\Sonos\Cloud\Interfaces\ApiInterface;
use duncan3dc\Sonos\Cloud\Interfaces\UserInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\SimpleCache\CacheInterface;
use function count;
use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function sys_get_temp_dir;

final class Api implements ApiInterface
{
    /** @var ClientInterface */
    private $client;

    /** @var OAuthProvider */
    private $provider;

    /**
     * Create a new instance.
     *
     * @param string $key
     * @param string $secret
     * @param string $redirect
     * @param ClientInterface|null $client
     */
    public function __construct(string $key, string $secret, string $redirect, ClientInterface $client = null)
    {
        if ($client === null) {
            $client = new Client(["http_errors" => false]);
        }
        $this->client = $client;

        $this->provider = new OAuthProvider([
            "clientId" => $key,
            "clientSecret" => $secret,
            "redirectUri" => $redirect,
            "httpClient" => $client,
        ]);
    }


    /**
     * Get the authorisation URL for the API.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->provider->getAuthorizationUrl();
    }


    /**
     * Generate an access token for the API.
     *
     * @param string $code
     *
     * @return AccessToken
     */
    public function getAccessToken(string $code): AccessToken
    {
        return $this->provider->getAccessToken("authorization_code", ["code" => $code]);
    }


    /**
     * @inheritDoc
     */
    public function request(string $method, string $url, array $data, AccessToken $token): array
    {
        $url = "https://api.ws.sonos.com/control/api/v1/{$url}";

        $request = $this->provider->getAuthenticatedRequest($method, $url, $token);

        if (count($data) > 0) {
            $json = json_encode($data);
            $stream = \GuzzleHttp\Psr7\stream_for($json);
            $request = $request->withBody($stream);
        }

        $response = $this->client->send($request);

        $result = json_decode((string) $response->getBody(), true);

        if (json_last_error() !== \JSON_ERROR_NONE) {
            throw new ApiException(json_last_error_msg(), json_last_error());
        }

        return $result;
    }


    /**
     * @inheritDoc
     */
    public function getUser(string $username, CacheInterface $cache = null): UserInterface
    {
        if ($cache === null) {
            $cache = new FilesystemPool(sys_get_temp_dir() . "/sonos");
        }

        return new User($username, $this, $cache);
    }
}
