<?php
namespace AuthActions\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Component\CookieComponent;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class AuthUtilsComponent extends Component
{
    public $components = ['Cookie', 'Auth'];

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
}
