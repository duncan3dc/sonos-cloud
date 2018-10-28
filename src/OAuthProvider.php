<?php

namespace duncan3dc\Sonos\Cloud;

use duncan3dc\Sonos\Cloud\Exceptions\AuthenticationException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use function array_key_exists;

final class OAuthProvider extends AbstractProvider
{
    /**
     * @inheritdoc
     */
    public function getBaseAuthorizationUrl()
    {
        return "https://api.sonos.com/login/v3/oauth";
    }

    /**
     * @inheritdoc
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return "https://api.sonos.com/login/v3/oauth/access";
    }

    /**
     * @inheritdoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return "https://api.sonos.com/login/v3/oauth/owner";
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScopes()
    {
        return ["playback-control-all"];
    }

    /**
     * @inheritdoc
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data["error"])) {
            $error = $data["error"];
            if (!empty($data["error_description"])) {
                $error .= " - " . $data["error_description"];
            }
            throw new AuthenticationException($error, $response->getStatusCode());
        }
    }

    /**
     * @inheritdoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, "id");
    }

    /**
     * Builds request options used for requesting an access token.
     *
     * @param  array $params
     * @return array
     */
    protected function getAccessTokenOptions(array $params)
    {
        $options = parent::getAccessTokenOptions($params);

        if (!array_key_exists("headers", $options)) {
            $options["headers"] = [];
        }

        $options["headers"]["Authorization"] = "Basic " . base64_encode($this->clientId . ":" . $this->clientSecret);

        return $options;
    }

    /**
     * Returns authorization headers for the bearer grant.
     *
     * @param  mixed|null $token Either a string or an access token instance
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        return ["Authorization" => "Bearer {$token}"];
    }
}
