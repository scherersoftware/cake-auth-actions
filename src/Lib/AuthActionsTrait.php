<?php
declare(strict_types = 1);
namespace AuthActions\Lib;

use Cake\Controller\ErrorController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;

trait AuthActionsTrait
{
    /**
     * @var \AuthActions\Lib\AuthActions
     */
    protected $_AuthActions;

    /**
     * @var \AuthActions\Lib\UserRights
     */
    protected $_UserRights;

    /**
     * Initializer, must be called in beforeFilter()
     *
     * @return void
     */
    public function initAuthActions(): void
    {
        EventManager::instance()->on(
            'Controller.beforeRender',
            function (Event $event): void {
                // Make the AuthComponent, AuthActions and UserRights available to the view.
                // FIXME - find a clean way to do this
                if (!$event->getSubject() instanceof ErrorController) {
                    $viewAuthActions = [
                        'AuthActions' => $event->getSubject()->getAuthActions(),
                        'UserRights' => $event->getSubject()->getUserRights(),
                    ];
                    $event->getSubject()->set('viewAuthActions', $viewAuthActions);
                }
            }
        );

        if (
            $this->getAuthActions()->isPublicAction(
                $this->getRequest()->getParam('prefix', ''),
                $this->getRequest()->getParam('plugin', ''),
                $this->getRequest()->getParam('controller', ''),
                $this->getRequest()->getParam('action', '')
            )
        ) {
            $this->Auth->allow();
        }
    }

    /**
     * Instance getter for the AuthActions instance
     *
     * @return \AuthActions\Lib\AuthActions
     */
    public function getAuthActions(): AuthActions
    {
        if (!$this->_AuthActions) {
            if (Configure::load('auth_actions') === false) {
                trigger_error('AuthActions: Could not load config/auth_actions.php', E_USER_WARNING);
            }

            $actionConfig = Configure::read('auth_actions');
            $publicActionsConfig = Configure::read('public_actions');
            $options = Configure::read('auth_settings');
            if (!is_array($options)) {
                $options = [];
            }
            if (!is_array($publicActionsConfig)) {
                $publicActionsConfig = [];
            }

            $this->_AuthActions = new AuthActions($actionConfig, $publicActionsConfig, $options);
        }

        return $this->_AuthActions;
    }

    /**
     * Instance getter for the UserRights instance
     *
     * @return \AuthActions\Lib\UserRights
     */
    public function getUserRights(): UserRights
    {
        if (!$this->_UserRights) {
            if (Configure::load('user_rights') === false) {
                trigger_error('UserRights: Could not load config/user_rights.php', E_USER_WARNING);
            }

            $rightsConfig = Configure::read('user_rights');
            if (!is_array($rightsConfig)) {
                $rightsConfig = [];
            }

            $this->_UserRights = new UserRights($rightsConfig);
        }

        return $this->_UserRights;
    }

    /**
     * See AuthActions::isAuthorized()
     *
     * @param array $user User to check
     * @return bool
     */
    public function isAuthorized(array $user): bool
    {
        return $this->getAuthActions()->isAuthorized(
            $this->Auth->user(),
            $this->getRequest()->getParam('prefix', ''),
            $this->getRequest()->getParam('plugin', ''),
            $this->getRequest()->getParam('controller', ''),
            $this->getRequest()->getParam('action', '')
        );
    }

    /**
     * See UserRights::userHasRight()
     *
     * @param string $right right name
     * @return bool
     */
    public function hasRight(string $right): bool
    {
        if ($this->Auth->user() === null) {
            return false;
        }

        return $this->getUserRights()->userHasRight($this->Auth->user(), $right);
    }
}
