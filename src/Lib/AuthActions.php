<?php

namespace AuthActions\Lib;

use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

class AuthActions
{

    /**
     * Holds the rights config.
     * Format:
     *        'controller' => array(
     *            'action1' => array(role1, role2)
     *        ),
     *        'controller2' => array(
     *            '*' => array('role3')
     *        )
     *
     * @var string
     */
    protected $_rightsConfig = array ();

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
     * @param array $rightsConfig  The controller-actions/rights configuration
     * @param array $publicActions Public actions
     * @param array $options       Additional options
     */
    public function __construct(array $rightsConfig, array $publicActions, array $options = [])
    {
        $this->_rightsConfig = $rightsConfig;
        $this->_publicActions = $publicActions;
        $this->_options = Hash::merge($this->_options, $options);
    }

    /**
     * Checks whether the user has access to certain controller action
     *
     * @param array $user user to check
     * @param array $route
     * @return bool
     */
    public function isAuthorized(array $user, array $route): bool
    {
        $key = $this->_getKeyFromRoute($route);

        if ($this->isPublicAction($route)) {
            return true;
        }

        return Auth::userIsAuthorized($user, $this->_rightsConfig[$key], $route['action']);
    }

    /**
     * Checks whether the user is allowed to access a specific URL
     *
     * @param array        $user user to check with
     * @param array|string $url  url to check
     * @return bool
     */
    public function urlAllowed(array $user, $url): bool
    {
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

        return $this->isAuthorized($user, $route);
    }

    public function isPublicAction(array $route): bool
    {
        $key = $this->_getKeyFromRoute($route);

        return Auth::isAuthorized($this->_publicActions[$key], $route['action']);
    }

    protected function _getKeyFromRoute(array $route): string
    {
        if ($this->_options['camelizedControllerNames']) {
            $controller = Inflector::camelize($route['controller']);
        } else {
            $controller = Inflector::underscore($route['controller']);
        }

        $key = $controller;
        if (!empty($route['plugin'])) {
            $key = $route['plugin'] . '.' . $key;
        }

        return $key;
    }
}
