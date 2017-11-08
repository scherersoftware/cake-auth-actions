<?php
namespace AuthActions\Lib;

use Cake\Routing\Router;
use Cake\Utility\Hash;
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
 * Options
 *
 * @var array
 */
	protected $_options = [
		'camelizedControllerNames' => false
	];

/**
 * Constructor
 *
 * @param array $rightsConfig The controller-actions/rights configuration
 * @param array $publicActions Public actions
 * @param array $options Additional options
 */
	public function __construct(array $rightsConfig, array $publicActions, array $options = []) {
		$this->_rightsConfig = $rightsConfig;
		$this->_publicActions = $publicActions;
		$this->_options = Hash::merge($this->_options, $options);
	}

/**
 * Checks whether the user has access to certain controller action
 *
 * @param array $user user to check
 * @param string $plugin plugin name or null
 * @param string $controller controller name
 * @param string $action action
 * @return bool
 */
	public function isAuthorized($user, $plugin, $controller, $action) {
		$isAuthorized = false;

		if ($plugin) {
			$plugin = Inflector::camelize($plugin);
		}

		if ($this->isPublicAction($plugin, $controller, $action)) {
			$isAuthorized = true;
		} elseif (isset($user['role']) && !empty($controller) && !empty($action)) {
			if ($this->_options['camelizedControllerNames']) {
				$controller = Inflector::camelize($controller);
			} else {
				$controller = Inflector::underscore($controller);
			}

			$key = $controller;
			if (!empty($plugin)) {
				$key = $plugin . '.' . $key;
			}

            $isAuthorized = $this->_isActionAuthorized($user, $this->_rightsConfig[$key], $action);
		}
		return $isAuthorized;
	}

/**
 * Checks whether the user has access to the given action for the given configuration
 *
 * @param array  $user   user to check
 * @param array  $config rights configuration
 * @param string $action action
 * @return bool
 */
    protected function _isActionAuthorized($user, $config, $action)
    {
        $permissions = [];

        if (isset($config['*'])) {
            $permissions = $config['*'];
        } elseif (isset($config[$action])) {
            $permissions = $config[$action];
        }

        if ($permissions === '*') {
            return true;
        }

        if (in_array($user['role'], $permissions)) {
            return true;
        }

        foreach ($permissions as $key => $permission) {
            if (is_array($permission)) {
                foreach ($permission as $path => $pathValue) {
                    if (!in_array(Hash::get($user, $path), $pathValue)) {
                        return false;
                    }
                }

                return true;
            }

            if (is_string($key)
                && is_array($permission)
                && in_array(Hash::get($user, $key), $permission)) {
                return true;
            }
        }

        return false;
    }

/**
 * Checks if the given plugin/controller/action combination is configured to be public
 *
 * @param string $plugin plugin name
 * @param string $controller controller name
 * @param string $action action name
 * @return bool
 */
	public function isPublicAction($plugin, $controller, $action) {
		if ($this->_options['camelizedControllerNames']) {
			$controller = Inflector::camelize($controller);
		} else {
			$controller = Inflector::underscore($controller);
		}
		$key = ($plugin ? $plugin . '.' : '') . $controller;

		$isPublic = false;
		if (isset($this->_publicActions[$key])) {
			if ($this->_publicActions[$key] === '*') {
				$isPublic = true;
			} elseif ($this->_publicActions[$key] === $action) {
				$isPublic = true;
			} elseif (is_array($this->_publicActions[$key]) && in_array($action, $this->_publicActions[$key])) {
				$isPublic = true;
			}
		}
		return $isPublic;
	}

/**
 * Checks whether the user is allowed to access a specific URL
 *
 * @param array $user user to check with
 * @param array|string $url url to check
 * @return void
 */
	public function urlAllowed($user, $url) {
		if (empty($url)) {
			return false;
		}

		if (is_array($url)) {
			// prevent plugin confusion
			$url = Hash::merge([
				'plugin' => null
			], $url);

			$url = Router::url($url);
			// strip off the base path
			$url = Router::normalize($url);
		}
		$route = Router::parse($url);
		if (empty($route['controller']) || empty($route['action'])) {
			return false;
		}
		return $this->isAuthorized($user, $route['plugin'], $route['controller'], $route['action']);
	}
}
