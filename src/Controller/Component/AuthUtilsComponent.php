<?php
declare(strict_types = 1);
namespace AuthActions\Controller\Component;

use Cake\Controller\Component;
use Cake\Http\Cookie\Cookie;
use DateTime;

/**
 * @property \Cake\Controller\Component\AuthComponent $Auth
 */
class AuthUtilsComponent extends Component
{
    /**
     * Other Components this component uses.
     *
     * @var array
     */
    public $components = ['Auth'];

    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'name' => 'remember_me',
        'expiresAt' => '+14 days',
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httpOnly' => true,
    ];

    /**
     * Add a Remeber me cookie
     *
     * @param string $userId User ID
     * @return void
     */
    public function addRememberMeCookie(string $userId): void
    {
        $this->getController()->setResponse(
            $this->getController()->getResponse()
                ->withCookie(new Cookie(
                    $this->getConfig('name'), // cookie name
                    $userId, // value
                    new DateTime($this->getConfig('expiresAt')), // expiration time
                    $this->getConfig('path'), // path
                    $this->getConfig('domain'), // domain
                    $this->getConfig('secure'), // secure
                    $this->getConfig('httpOnly') // httponly
                ))
        );
    }

    /**
     * Deletes the remember me cookie
     *
     * @return void
     */
    public function destroyRememberMeCookie(): void
    {
        $this->getController()->setResponse(
            $this->getController()
                ->getResponse()
                ->withExpiredCookie($this->getController()
                    ->getRequest()
                    ->getCookieCollection()
                    ->get($this->getConfig('name')))
        );
    }

    /**
     * Check if a remeber me cookie exists and login the user
     *
     * @return mixed User ID on success, false if no valid cookie
     */
    public function checkRememberMeCookie()
    {
        if ($this->loggedIn() === false
            && $this->getController()->getRequest()->getCookie($this->getConfig('name')) !== null
        ) {
            return $this->getController()->getRequest()->getCookie($this->getConfig('name'));
        }

        return false;
    }

    /**
     * Determines if the user is logged in
     *
     * @return bool
     */
    public function loggedIn(): bool
    {
        return $this->Auth->user() !== null;
    }
}
