<?php
declare(strict_types = 1);

namespace AuthActions\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Cake\Utility\Hash;

/**
 * @property \Cake\Controller\Component\AuthComponent $Auth
 * @property \Cake\Controller\Component\CookieComponent $Cookie
 */
class AuthUtilsComponent extends Component
{
    /**
     * Required components
     *
     * @var array
     */
    public $components = ['Cookie', 'Auth'];

    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'defaultRedirect' => '/'
    ];

    /**
     * Add a Remember me cookie
     *
     * @param string $userId  UserID
     * @param string $options Options array for the cookie config
     * @return void
     */
    public function addRememberMeCookie(string $userId, array $options = []): void
    {
        $options = Hash::merge([
            'expires' => '+14 days',
            'httpOnly' => true,
            'secure' => false
        ], $options);

        $this->Cookie->setConfig($options);
        $this->Cookie->write('User.id', $userId);
    }

    /**
     * Deletes the remember me cookie
     *
     * @return void
     */
    public function destroyRememberMeCookie(): void
    {
        $this->Cookie->delete('User');
    }

    /**
     * Check if a remeber me cookie exists and login the user
     *
     * @return null|string
     */
    public function checkRememberMeCookie(): ?string
    {
        if (!$this->loggedIn() && $this->Cookie->read('User.id')) {
            return $this->Cookie->read('User.id');
        }

        return null;
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

    /**
     * Attempts to auto login a user and returns a redirect on success.
     *
     * @param \Cake\Datasource\EntityInterface $user User
     * @return \Cake\Http\Response|null
     */
    public function autoLogin(EntityInterface $user): ?Response
    {
        $controller = $this->getController();
        $request = $controller->request;
        $token = $request->getQuery('t');
        if (empty($token)) {
            return null;
        }

        $this->Auth->logout();
        $tokenData = $user->validateLoginToken($token, $user->getKey(), $user->getSalt());
        if (!is_array($tokenData)) {
            return null;
        }
        if (!empty($tokenData['addRememberMeCookie']) && $tokenData['addRememberMeCookie']) {
            $this->addRememberMeCookie((string)$user->id);
        }
        $userData = $user->toArray();
        $userData['user'] = $user;
        $this->Auth->setUser($userData);
        if (!empty($tokenData['url'])) {
            return $controller->redirect($tokenData['url']);
        }

        return $controller->redirect($this->getConfig('defaultRedirect'));
    }
}
