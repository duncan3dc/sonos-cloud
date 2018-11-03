<?php

namespace duncan3dc\Sonos\Cloud\Interfaces;

interface UserInterface extends ClientInterface
{
    /**
     * Check if we have access to this users account.
     *
     * @return bool
     */
    public function isAuthorised(): bool;

    /**
     * Get the URL to send the user to for authorisation.
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Set the user's authorisation code after redirect.
     *
     * @param string $code The code returned by the successful authorisation
     *
     * @return void
     */
    public function setCode(string $code): void;
}
