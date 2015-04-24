<?php
namespace AuthActions\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Component\CookieComponent;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class AuthUtilsComponent extends Component
{
    public $components = ['Cookie', 'Auth', 'Flash'];
    
    /**
     * Add a Remeber me cookie
     *
     * @param string $userId UserID
     * @param string $options Options array for the cookie config
     * @return void
     */
    public function addRemeberMeCookie($userId, $options = [])
    {
        $options = Hash::merge([
            'expires' => '+14 days',
            'httpOnly' => true,
            'secure' => false
        ], $options);
        
        $this->Cookie->config([$options]);
        $this->Cookie->write('User.id', $userId);
    }

    public function destroyRememberMeCookie() {
        $this->Cookie->delete('User');
    }

    /**
     * Check if a remeber me cookie exists and login the user
     *
     * @return void
     */
    public function checkRemeberMeCookie()
    {
        if (!$this->Auth->user() && $this->Cookie->read('User.id')) {
            $user = TableRegistry::get('Users')->get($this->Cookie->read('User'))->toArray();
            $this->Auth->setUser($user);
        }
    }
} 