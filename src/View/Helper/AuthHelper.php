<?php
declare(strict_types = 1);
namespace AuthActions\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\View;

class AuthHelper extends Helper
{

    /**
     * Session key
     *
     * @var string
     */
    public $sessionKey = 'Auth.User';

    /**
     * View auth actions
     *
     * @var array
     */
    protected $_viewAuth;

    /**
     * Configures the instance
     *
     * @param \Cake\View\View $view CakePHP view object
     * @param array $config helper config
     */
    public function __construct(View $view, array $config = [])
    {
        parent::__construct($view, $config);

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
            return $this->sessionKey && $this->getView()->getRequest()->getSession()->check($this->sessionKey);
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
        if ($this->sessionKey && $this->getView()->getRequest()->getSession()->check($this->sessionKey)) {
            $user = $this->getView()->getRequest()->getSession()->read($this->sessionKey);
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
     * @param string|array $url url to check
     * @return bool
     */
    public function urlAllowed($url): bool
    {
        if ($this->_viewAuth) {
            return $this->_viewAuth['AuthActions']->urlAllowed($this->user(), $url);
        }

        return false;
    }
}
