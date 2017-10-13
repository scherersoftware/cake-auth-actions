<?php
namespace AuthActions\Controller\Component;

use AuthActions\Lib\AutoLogin;
use Cake\Controller\Component;
use Cake\Controller\Component\CookieComponent;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Http\Response;

class AuthUtilsComponent extends Component
{
    public $components = ['Cookie', 'Auth'];

    protected $_defaultConfig = [
        'defaultRedirect' => '/'
    ];

    /**
     * Add a Remeber me cookie
     *
     * @param string $userId UserID
     * @param string $options Options array for the cookie config
     * @return void
     */
    public function addRememberMeCookie($userId, $options = [])
    {
        $options = Hash::merge([
            'expires' => '+14 days',
            'httpOnly' => true,
            'secure' => false
        ], $options);

        $this->Cookie->config($options);
        $this->Cookie->write('User.id', $userId);
    }

    /**
     * Deletes the remember me cookie
     *
     * @return void
     */
    public function destroyRememberMeCookie()
    {
        $this->Cookie->delete('User');
    }

    /**
     * Check if a remeber me cookie exists and login the user
     *
     * @return mixed User ID on success, false if no valid cookie
     */
    public function checkRememberMeCookie()
    {
        if (!$this->loggedIn() && $this->Cookie->read('User.id')) {
            return $this->Cookie->read('User.id');
        }
        return false;
    }

    /**
     * Determines if the user is logged in
     *
     * @return bool
     */
    public function loggedIn()
    {
        return $this->Auth->user() !== null;
    }

    /**
     * Attempts to auto login a user and returns a redirect on success.
     *
     * @param \Cake\Datasource\EntityInterface $user User
     * @param string                           $key  Seurity key (should be user specific)
     * @param string                           $salt Security salt (should be user specific)
     * @return \Cake\Http\Response|null
     */
    public function autoLogin(EntityInterface $user, string $key, string $salt): ?Response
    {
        $controller = $this->getController();
        $request = $controller->request;
        $token = $request->getQuery('t');
        if (empty($token)) {
            return null;
        }
        $controller->Auth->logout();
        $tokenData = AutoLogin::validateLoginToken($token, $key, $salt);
        if (!is_array($tokenData)) {
            return null;
        }
        if (!empty($tokenData['addRememberMeCookie']) && $tokenData['addRememberMeCookie']) {
            $controller->AuthUtils->addRememberMeCookie($user->id);
        }
        $userData = $user->toArray();
        $userData['user'] = $user;
        $controller->Auth->setUser($userData);
        if (!empty($tokenData['url'])) {
            return $controller->redirect($tokenData['url']);
        }

        return $controller->redirect($this->getConfig('defaultRedirect'));
    }
}
