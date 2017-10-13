<?php

namespace AuthActions\Lib;


trait AutoLoginTrait
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
    ): string {
        return AutoLogin::getAutoLoginUrl(
            $this->getKey(),
            $this->getSalt(),
            $autoUrl,
            $redirectUrl,
            $expireInterval,
            $addRememberMeCookie
        );
    }

    /**
     * Returns the key for the auto login url
     *
     * @return string
     * @throws \Exception
     */
    public function getKey(): string
    {
        return $this->user_key;
    }

    /**
     * Returns the salt for the auto login url
     *
     * @return string
     * @throws \Exception
     */
    public function getSalt(): string
    {
        return $this->user_salt;
    }
}
