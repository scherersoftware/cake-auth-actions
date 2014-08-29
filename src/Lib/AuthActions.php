<?php
namespace AuthActions\Lib;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

class AuthActions {

	/**
	 * Holds the rights config.
	 *
	 * Format:
	 * 		'controller' => array(
	 * 			'action1' => array(role1, role2)
	 * 		),
	 * 		'controller2' => array(
	 * 			'*' => array('role3')
	 * 		)
	 *
	 * @var string
	 */
	protected $_rightsConfig = array();
	
	/**
	 * Holds the public actions config
	 *
	 * @var array
	 */	
	protected $_publicActions = [];

	/**
	 * Constructor
	 *
	 * @param array $rightsConfig	The controller-actions/rights configuration
	 */
	public function __construct(array $rightsConfig, array $publicActions) {
		$this->_rightsConfig = $rightsConfig;
		$this->_publicActions = $publicActions;
	}

	/**
	 * Checks whether the user has access to certain controller action
	 *
	 * @param array $user
	 * @param string $controller
	 * @param string $action
	 * @return bool
	 */
	public function isAuthorized($user, $plugin, $controller, $action) {
		$isAuthorized = false;

		if($plugin) {
			$plugin = Inflector::camelize($plugin);
		}

		if($this->isPublicAction($plugin, $controller, $action)) {
			$isAuthorized = true;
		}
		else if(isset($user['role']) && !empty($controller) && !empty($action)) {
			$controller = Inflector::underscore($controller);

			$key = $controller;
			if(!empty($plugin)) {
				$key = $plugin . '.' . $key;
			}

			if(isset($this->_rightsConfig[ $key ]['*']) && $this->_rightsConfig[ $key ]['*'] == '*') {
				$isAuthorized = true;
			}
			else if(isset($this->_rightsConfig[ $key ]['*'])
				&& in_array($user['role'], $this->_rightsConfig[ $key ]['*'])) {
				$isAuthorized = true;
			}
			else if(isset($this->_rightsConfig[ $key ][ $action ])
					&& in_array($user['role'], $this->_rightsConfig[ $key ][ $action ])) {

				$isAuthorized = true;
			}
		}
		return $isAuthorized;
	}

/**
 * @param string $plugin 
 * @param string $controller 
 * @param string $action 
 * @return bool
 */	
	public function isPublicAction($plugin, $controller, $action) {
		$controller = Inflector::underscore($controller);
		
		$key = ($plugin ? $plugin . '.' : '') . $controller;

		$isPublic = false;
		if(isset($this->_publicActions[ $key ])) {
			if($this->_publicActions[ $key ] === '*') {
				$isPublic = true;
			}
			else if($this->_publicActions[ $key ] === $action) {
				$isPublic = true;
			}
			else if(is_array($this->_publicActions[ $key ]) && in_array($action, $this->_publicActions[ $key ])) {
				$isPublic = true;
			}
		}
		return $isPublic;
	}

	/**
	 * Checks whether the user is allowed to access a specific URL
	 *
	 * @param array $user
	 * @param array|string $url
	 * @return void
	 * @author Robert Scherer
	 */
	public function urlAllowed($user, $url) {
		if(empty($url)) {
			return false;
		}

		if(is_array($url)) {
			// prevent plugin confusion
			$url = Hash::merge([
				'plugin' => null
			], $url);

			$url = Router::url($url);
			// strip off the base path
			$url = Router::normalize($url);
		}
		$route = Router::parse($url);
		if(empty($route['controller']) || empty($route['action'])) {
			return false;
		}
		return $this->isAuthorized($user, $route['plugin'], $route['controller'], $route['action']);
	}
}
