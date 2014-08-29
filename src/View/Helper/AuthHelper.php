<?php
namespace AuthActions\View\Helper;
use Cake\View\Helper;

class AuthHelper extends AppHelper {

/**
 * whether the user is logged in
 *
 * @return bool
 */
	public function isLoggedIn() {
		$auth = ClassRegistry::getObject('AuthComponent');
		return $auth->loggedIn();
	}

/**
 * Accessor to the logged in user's properties
 *
 * @param string $key 
 * @return mixed
 */
	public function user($key = null) {
		$auth = ClassRegistry::getObject('AuthComponent');
		return $auth->user($key);
	}

/**
 * Returns whether the user has the given $right
 *
 * @param string $right 
 * @return bool
 */
	public function hasRight($right) {
		$auth = ClassRegistry::getObject('AuthComponent');	
		return $auth->hasRight($right);
	}

/**
 * @param string|array $url 
 * @return bool
 */	
	public function urlAllowed($url) {
		$auth = ClassRegistry::getObject('AuthComponent');
		return $auth->urlAllowed($url);
	}
}