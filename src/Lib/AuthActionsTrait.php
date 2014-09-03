<?php
namespace AuthActions\Lib;
use \Cake\Core\Configure;
use Cake\Event\EventManager;


trait AuthActionsTrait {
	
/**
 * @var AuthActions
 */	
	protected $_AuthActions;
	
/**
 * @var UserRights
 */	
	protected $_UserRights;

	public function initAuthActions() {
		EventManager::instance()->attach(function(\Cake\Event\Event $event) {
			// Make the AuthComponent, AuthActions and UserRights available to the view.
			// FIXME - find a clean way to do this
			if(!$event->subject() instanceof \Cake\Controller\ErrorController) {
				$viewAuthActions = [
					'AuthActions' => $event->subject()->getAuthActions(),
					'UserRights' => $event->subject()->getUserRights()
				];
				$event->subject()->set('viewAuthActions', $viewAuthActions);
			}
		}, 'Controller.beforeRender');
		
		if($this->getAuthActions()->isPublicAction($this->request->params['plugin'], $this->request->params['controller'], $this->request->params['action'])) {
			$this->Auth->allow();
		}
	}

	public function getAuthActions() {
		if(!$this->_AuthActions) {
			if(Configure::load('auth_actions') === false) {
				trigger_error('AuthActions: Could not load config/auth_actions.php', E_USER_WARNING);	
			}

			$actionConfig = Configure::read('auth_actions');
			$publicActionsConfig = Configure::read('public_actions');
			if(!is_array($publicActionsConfig)) {
				$publicActionsConfig = [];
			}

			$this->_AuthActions = new AuthActions($actionConfig, $publicActionsConfig);
		}
		return $this->_AuthActions;
	}

	public function getUserRights() {
		if(!$this->_UserRights) {
			if(Configure::load('user_rights') === false) {
				trigger_error('UserRights: Could not load config/user_rights.php', E_USER_WARNING);	
			}

			$rightsConfig = Configure::read('user_rights');
			if(!is_array($rightsConfig)) {
				$rightsConfig = [];
			}

			$this->_UserRights = new UserRights($rightsConfig);
		}
		return $this->_UserRights;
	}

	public function isAuthorized($user) {
		return $this->getAuthActions()->isAuthorized(
			$this->Auth->user(),
			$this->request->params['plugin'], 
			$this->request->params['controller'], 
			$this->request->params['action']
		);
	}
	
	public function hasRight($right) {
		if($this->Auth->user() !== null) {
			return $this->getUserRights()->userHasRight($this->Auth->user(), $right);
		}
		return false;
	}
}