<?php
namespace AuthActions\Lib;
use \Cake\Core\Configure;

trait AuthActionsTrait {
	
/**
 * @var AuthActions
 */	
	protected $_AuthActions;

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

	public function initAuthActions() {
		
		if($this->getAuthActions()->isPublicAction($this->request->params['plugin'], $this->request->params['controller'], $this->request->params['action'])) {
			$this->Auth->allow();
		}
	}

	public function isAuthorized($user) {
		return $this->getAuthActions()->isAuthorized(
			$this->Auth->user(),
			$this->request->params['plugin'], 
			$this->request->params['controller'], 
			$this->request->params['action']
		);
	}
}