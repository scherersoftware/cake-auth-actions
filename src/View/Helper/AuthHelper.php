<?php
namespace AuthActions\View\Helper;
use Cake\View\Helper;

class AuthHelper extends Helper {

	public $sessionKey = 'Auth.User';
	protected $_viewAuth;
	public $helpers = ['Session'];
	
	public function __construct(\Cake\View\View $View, array $config = array()) {
		parent::__construct($View, $config);

		$this->_viewAuth = $this->_View->get('viewAuthActions');
	}

/**
 * whether the user is logged in
 *
 * @return bool
 */
	public function loggedIn() {
		if($this->_viewAuth) {
			return $this->_viewAuth['AuthComponent']->user() !== null;
		}
		return false;
	}

/**
 * Accessor to the logged in user's properties
 *
 * @param string $key 
 * @return mixed
 */
	public function user($key = null) {
		if ($this->sessionKey && $this->Session->check($this->sessionKey)) {
			$user = $this->Session->read($this->sessionKey);
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
 * @param string $right 
 * @return bool
 */
	public function hasRight($right) {
		if($this->_viewAuth) {
			return $this->_viewAuth['UserRights']->userHasRight($this->user(), $right);
		}
		return false;
	}

/**
 * @param string|array $url 
 * @return bool
 */	
	public function urlAllowed($url) {
		if($this->_viewAuth) {
			return $this->_viewAuth['AuthActions']->urlAllowed($this->user(), $url);
		}
		return false;
	}
}