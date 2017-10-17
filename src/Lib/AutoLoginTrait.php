<?php

namespace AuthActions\Lib;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;
use Cake\Utility\Security;

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
     * @throws \Exception If the generated URL is too long for browsers
     */
    public function getAutoLoginUrl(
        array $autoUrl,
        array $redirectUrl = null,
        string $expireInterval = '1 day',
        bool $addRememberMeCookie = true
    ): string {
        $autoUrl['?']['t'] = $this->generateLoginToken(
            $redirectUrl,
            $expireInterval,
            $addRememberMeCookie
        );
        $url = Router::url($autoUrl, true);
        $urlLength = strlen($url);
        if (strlen($url) > 2080) {
            throw new \Exception('Generated url "' . $url . '" is too long');
        }

        return $url;
    }


    /**
     * Validates the token.
     *
     * @param string $token Token
     * @param string $key   Security key
     * @param string $salt  Security salt
     * @return array|null
     */
    public function validateLoginToken(string $token): ?array
    {
        $token = base64_decode($token);
        $serializedData = Security::decrypt($token, $this->getKey(), $this->getSalt());
        if ($serializedData === false) {
            return null;
        }
        $data = unserialize($serializedData);
        if (!empty($data['expireInterval']) && !empty($data['timestamp'])) {
            $tokenCreated = new Time($data['timestamp']);
            if (!$tokenCreated->wasWithinLast($data['expireInterval'])) {
                return null;
            }
        }

        return $data;
    }

    /**
     * Generates a auto login token.
     *
     * @param array|null $redirectUrl         Optional redirect url
     * @param string     $expireInterval      When this token expires
     * @param bool       $addRememberMeCookie Enabling setting the remember me cookie on auto login
     * @return string
     */
    public function generateLoginToken(
        array $redirectUrl = null,
        string $expireInterval = '1 day',
        bool $addRememberMeCookie = true
    ): string {
        $data = [
            'url' => $redirectUrl,
            'timestamp' => Time::now()->toUnixString(),
            'expireInterval' => $expireInterval,
            'addRememberMeCookie' => $addRememberMeCookie
        ];
        $serializedData = serialize($data);
        $token = Security::encrypt($serializedData, $this->getKey(), $this->getSalt());

        return base64_encode($token);
    }

    /**
     * Returns the key for the auto login url encryption
     *
     * @return string
     * @throws \Exception
     */
    public function getKey(): string
    {
        return $this->user_key;
    }

    /**
     * Returns the salt for the auto login url encryption
     *
     * @return string
     * @throws \Exception
     */
    public function getSalt(): string
    {
        if (Configure::load('auth_actions') === false) {
            trigger_error('UserRights: Could not load config/user_rights.php', E_USER_WARNING);
        }

        return Configure::read('auto_login.salt');
    }
}
