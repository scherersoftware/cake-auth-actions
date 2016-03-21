cake-auth-actions
====================================

A simple, configuration based ACL alternative for CakePHP 3. Allows you to define specific access rights to controller actions for different kinds of users.

## Installation

#### 1. require the plugin in your `composer.json`

		"require": {
			"codekanzlei/cake-auth-actions": "dev-master",
		}

#### 2. Include the plugin using composer
Open a terminal in your project-folder and run these commands:

	$ composer update
	
#### 3. Load the plugin in your `config/bootstrap.php`

	Plugin::load('AuthActions', ['bootstrap' => false, 'routes' => false]);

## Usage & Configuration

#### 1. Configure `AppController.php`

In your `src/Controller/AppController.php`, insert the following pieces of code in the matching sections:

**Traits:**

	use \AuthActions\Lib\AuthActionsTrait;

**$helpers:**

	public $helpers = [
		'Auth' => ['className' => 'AuthActions.Auth']
	]

**$components:**	

	public $components = [
	    'Auth' => [
            'authenticate' => [
                'Form' => [
                    'repository' => 'Users',
                    'scope' => [
                        'status' => Status::ACTIVE,
                    ]
                ]
            ],
            'authorize' => ['Controller'],
            'loginAction' => [], // prefered login view
            'loginRedirect' => [], // redirect after successful login
            'logoutRedirect' => [], // redirect after successful logout
            'authError' => 'PERMISSION_DENIED',
            
            // namespace declaration of AuthUtilsComponent
            'AuthActions.AuthUtils'
        ]
  
**beforeFilter():**

    public function beforeFilter(\Cake\Event\Event $event)
    {
        $this->initAuthActions();
    }	

#### 2. Create additional files
In your project's `config` folder, create the required config files. 

**Note:** For reference, see these files:

- `auth_actions.php-default`

	here you can grant or restrict access to Controller functions to certain user roles.

- `user_rights.php-default`

	here you can define further custom access rights, allowing easy control over which buttons will be rendered in view files, depending on the role of the user that's viewing them.
	
See [4. Grant/Restrict group rights](#### 4. Grant/Restrict group rights) for further information and example code snippets.

**auth_actions.php**

	$ touch config/auth_actions.php

**user_rights.php**

	$ touch config/user_rights.php

#### 3. Define custom user roles

In your `User.php`, you can define custom user roles as constants.

A commonly used, basic set of user roles ADMIN and USER can be defined as follows:

    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';
    
Set the field `role` accessible, like so:

	 protected $_accessible = [
	 	'role' => true
	 ];

#### 4. Grant/Restrict group rights

Following the example of a simple USER and ADMIN setup above, consider the following commonly needed use-cases.

- **restricting access for non-admin users:**
	Consider a basic "Users" MVC setup. Assuming you wish to only grant ADMINS access to every controller-action, including edit() as well as any functions added later on, while restricting USERS from all functions except for index() and view().
	
	In `auth_actions.php`:
	
		$config = [
		    'auth_actions' => [
		    	// Controller name: 'Users'
		        'Users' => [
		        	// wildcard * includes every action in this controller
		            '*' => [
		                User::ROLE_ADMIN
		            ],
		            
		            // here we explicitly list actions that
		            // USERS shall be able to access 
		            'index' => [
		                User::ROLE_USER
		            ],
		            'view' => [
		                User::ROLE_USER
		            ]
		        ]
		    ]
		];
	
- **preventing buttons from being rendered in a view:** The above code will prevent USERS from calling any action in UsersController except for index() and view() but - for example - edit buttons next to User entities in your index-view will still be rendered. Here's how you can prevent them from being rendered if the view file is being viewes by a non-ADMIN user:

	In `user_rights.php`:
	
		$config = [
		    'user_rights' => [
		    	// granting a custom right only for Users of type ADMIN
		        'viewEditButton' => [
		            User::ROLE_ADMIN
		        ]
		    ]
		];

	In your index view:

        <?php if ($this->Auth->hasRight('viewEditButton')): ?>
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id]) ?>
        <?php endif; ?>