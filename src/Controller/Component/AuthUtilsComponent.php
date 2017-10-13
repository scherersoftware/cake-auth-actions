<?php
declare(strict_types = 1);
namespace AuthActions\Controller\Component;

use Cake\Controller\Component;
use Cake\Utility\Hash;

class AuthUtilsComponent extends Component
{
    /**
     * Used Components
     *
     * @var array
     */
    public $components = ['Cookie', 'Auth'];

    /**
     * Add a Remeber me cookie
     *
     * @param string $userId UserID
     * @param string $options Options array for the cookie config
     * @return void
     */
    public function addRememberMeCookie(string $userId, string $options = []): void
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
    public function destroyRememberMeCookie(): void
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
    public function loggedIn(): bool
    {
        return $this->Auth->user() !== null;
    }
}
