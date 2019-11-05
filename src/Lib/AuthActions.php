<?php
declare(strict_types = 1);
namespace AuthActions\Lib;

use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

class AuthActions
{

    /**
     * Holds the rights config.
     *
     * Format:
     *      'controller' => [
     *          'action1' => [role1, role2]
     *      ],
     *      'controller2' => [
     *          '*' => ['role3']
     *      ]
     *
     * @var array
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
    protected $_options = [
        'camelizedControllerNames' => false,
    ];

    /**
     * Constructor
     *
     * @param array $rightsConfig The controller-actions/rights configuration
     * @param array $publicActions Public actions
     * @param array $options Additional options
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
     * @param string $prefix prefix param
     * @param string $plugin plugin param
     * @param string $controller controller param
     * @param string $action action param
     * @return bool
     */
    public function isAuthorized(array $user, string $prefix, string $plugin, string $controller, string $action): bool
    {
        $isAuthorized = false;

        if ($plugin) {
            $plugin = Inflector::camelize($plugin);
        }

        if ($this->isPublicAction($prefix, $plugin, $controller, $action)) {
            $isAuthorized = true;
        } elseif (isset($user['role']) && !empty($controller) && !empty($action)) {
            if ($this->_options['camelizedControllerNames']) {
                $controller = Inflector::camelize($controller);
            } else {
                $controller = Inflector::underscore($controller);
            }

            $key = $controller;
            if (!empty($prefix)) {
                $key = $prefix . '/' . $key;
            }
            if (!empty($plugin)) {
                $key = $plugin . '.' . $key;
            }

            if (isset($this->_rightsConfig[$key]['*']) && $this->_rightsConfig[$key]['*'] == '*') {
                $isAuthorized = true;
            } elseif (isset($this->_rightsConfig[$key]['*'])
                && in_array($user['role'], $this->_rightsConfig[$key]['*'])
            ) {
                $isAuthorized = true;
            } elseif (isset($this->_rightsConfig[$key][$action])
                && in_array($user['role'], $this->_rightsConfig[$key][$action])
            ) {
                $isAuthorized = true;
            }
        }

        return $isAuthorized;
    }

    /**
     * Checks if the given plugin/controller/action combination is configured to be public
     *
     * @param string $prefix prefix param
     * @param string $plugin plugin param
     * @param string $controller controller param
     * @param string $action action param
     * @return bool
     */
    public function isPublicAction(string $prefix, string $plugin, string $controller, string $action): bool
    {
        $isPublic = false;

        if ($this->_options['camelizedControllerNames']) {
            $controller = Inflector::camelize($controller);
        } else {
            $controller = Inflector::underscore($controller);
        }

        $key = $controller;
        if (!empty($prefix)) {
            $key = $prefix . '/' . $key;
        }
        if (!empty($plugin)) {
            $key = $plugin . '.' . $key;
        }

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
                'plugin' => null,
            ], $url);

            $url = Router::url($url);
            // strip off the base path
            $url = Router::normalize($url);
        }

        $route = Router::getRouteCollection()->parse($url);

        if (empty($route['controller']) || empty($route['action'])) {
            return false;
        }

        return $this->isAuthorized(
            $user,
            $route['prefix'] ?? '',
            $route['plugin'] ?? '',
            $route['controller'] ?? '',
            $route['action'] ?? ''
        );
    }
}
