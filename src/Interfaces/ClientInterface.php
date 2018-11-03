<?php

namespace duncan3dc\Sonos\Cloud\Interfaces;

interface ClientInterface
{
    /**
     * Send a request to the API.
     *
     * @param string $method The HTTP method of the request
     * @param string $url The endpoint to send the request to
     * @param array $data Any additional data to send with the request
     *
     * @return \stdClass
     */
    public function request(string $method, string $url, array $data = []): \stdClass;
}
