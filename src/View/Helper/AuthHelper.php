<?php
declare(strict_types = 1);
namespace AuthActions\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\View;

class AuthHelper extends Helper
{

    /**
     * @var string
     */
    public $sessionKey = 'Auth.User';

    /**
     * @var array
     */
    protected $_viewAuth;

    /**
     * Configures the instance
     *
     * @param \Cake\View\View  $View   CakePHP view object
     * @param array $config helper config
     */
    public function __construct(View $View, array $config = [])
    {
        parent::__construct($View, $config);

        $this->_viewAuth = $this->_View->get('viewAuthActions');
    }

    /**
     * whether the user is logged in
     *
     * @return bool
     */
    public function loggedIn(): bool
    {
        if ($this->_viewAuth) {
            return $this->sessionKey && $this->request->getSession()->check($this->sessionKey);
        }

        return false;
    }

    /**
     * Accessor to the logged in user's properties
     *
     * @param string $key Key to read
     * @return mixed
     */
    public function user(string $key = null)
    {
        if ($this->sessionKey && $this->request->getSession()->check($this->sessionKey)) {
            $user = $this->request->getSession()->read($this->sessionKey);
        } else {
            return null;
        }
        if ($key === null) {
            return $user;
        }

        return Hash::get($user, $key);
    }

    /**
     * Returns whether the user has the given $right
     *
     * @param string $right name of right
     * @return bool
     */
    public function hasRight(string $right): bool
    {
        $user = $this->user();
        if ($this->_viewAuth && !is_null($user)) {
            return $this->_viewAuth['UserRights']->userHasRight($user, $right);
        }

        return false;
    }

    /**
     * Checks whether the URL is allowed for the currently logged in user
     *
     * @param array $url url to check
     * @return bool
     */
    public function urlAllowed(array $url): bool
    {
        if ($this->_viewAuth) {
            return $this->_viewAuth['AuthActions']->urlAllowed($this->user(), $url);
        }

        return false;
    }
}
