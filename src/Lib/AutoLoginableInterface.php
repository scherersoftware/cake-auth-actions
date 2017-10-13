<?php

namespace AuthActions\Lib;

interface AutoLoginableInterface
{
    /**
     * Returns a full auto login url with token.
     *
     * @param array      $autoUrl             URL configuration pointing to auto login page
     * @param array|null $redirectUrl         Optional redirect url
     * @param string     $expireInterval      When this token expires
     * @param bool       $addRememberMeCookie Enabling setting the remember me cookie on auto login
     * @return string
     * @throws \Exception
     */
    public function getAutoLoginUrl(
        array $autoUrl,
        array $redirectUrl = null,
        string $expireInterval = '1 day',
        bool $addRememberMeCookie = true
    ): string;

    /**
     * Validates the token.
     *
     * @param string $token Token
     * @param string $key   Security key
     * @param string $salt  Security salt
     * @return array|null
     */
    public function validateLoginToken(string $token): ?array;

    /**
     * Returns the key for the auto login url
     *
     * @return string
     * @throws \Exception
     */
    public function getKey(): string;

    /**
     * Returns the salt for the auto login url
     *
     * @return string
     * @throws \Exception
     */
    public function getSalt(): string;
}
