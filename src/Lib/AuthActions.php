<?php
declare(strict_types = 1);

namespace AuthActions\Lib;

use Cake\Core\InstanceConfigTrait;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

class AuthActions
{
    use InstanceConfigTrait;

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
    protected $_rightsConfig = [];

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
    protected $_defaultConfig = [
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
        $this->setConfig($options);
    }

    /**
     * Checks whether the user has access to certain controller action
     *
     * @param array $user user to check
     * @param array $route Route
     * @return bool
     */
    public function isAuthorized(array $user, array $route): bool
    {
        $key = $this->_getKeyFromRoute($route);

        if ($this->isPublicAction($route)) {
            return true;
        }

        return Auth::isAuthorized($this->_rightsConfig[$key], $route['action'], $user);
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

    /**
     * Returns whether the given route is a public action.
     *
     * @param array $route Route
     * @return bool
     */
    public function isPublicAction(array $route): bool
    {
        $key = $this->_getKeyFromRoute($route);

        return Auth::isAuthorized($this->_publicActions[$key], $route['action']);
    }

    /**
     * Returns the array key for the given route array
     *
     * @param array $route Route
     * @return string
     */
    protected function _getKeyFromRoute(array $route): string
    {
        if ($this->getConfig('camelizedControllerNames')) {
            $controller = Inflector::camelize($route['controller']);
        } else {
            $controller = Inflector::underscore($route['controller']);
        }

        if (isset($route['prefix'])) {
            $prefix = Inflector::camelize($route['prefix']);
            if (!empty($prefix)) {
                $controller = $prefix . '/' . $controller;
            }
        }

        $key = $controller;
        if (!empty($route['plugin'])) {
            $key = $route['plugin'] . '.' . $key;
        }

        return $key;
    }
}
