<?php
declare(strict_types = 1);

namespace AuthActions\Test\TestCase\Lib;

use AuthActions\Lib\Auth;
use Cake\TestSuite\TestCase;

class AuthTest extends TestCase
{
    public function testAuthForPublicActions(): void
    {
        $publicActions = [
            'home' => [
                'action_1',
                'action_2'
            ],
            'pages' => ['*'],
            'Cms.sitemap' => ['*'],
            'complex_1' => [
                '*' => function () {
                    return true;
                }
            ],
            'complex_2' => [
                '*' => function () {
                    return false;
                }
            ],
            'complex_3' => [
                '*' => function () {
                    return true;
                },
                'foo',
                'bar'
            ],
            'complex_4' => [
                'foo',
                'bar' => function () {
                    return true;
                }
            ],
            'complex_5' => [
                'foo',
                'bar' => true
            ],
            'complex_6' => [
                'foo',
                'bar' => [
                    'AND' => [
                        'foo' => true,
                        'baz' => true
                    ]
                ]
            ],
            'complex_7' => [
                'foo',
                'bar' => [
                    'AND' => [
                        'foo' => true,
                        'baz' => false
                    ]
                ]
            ],
            'complex_8' => [
                'foo',
                'bar' => [
                    'OR' => [
                        'foo' => true,
                        'baz' => false
                    ]
                ]
            ],
            'complex_9' => [
                'foo',
                'bar' => [
                    [
                        'foo' => true,
                        'baz' => false
                    ]
                ]
            ],
            'complex_10' => [
                'foo',
                'bar' => [
                    [
                        'foo' => false,
                        'baz' => false
                    ]
                ]
            ],
        ];

        $this->assertTrue(Auth::isAuthorized($publicActions['home'], 'action_1'));
        $this->assertFalse(Auth::isAuthorized($publicActions['home'], 'false'));
        $this->assertTrue(Auth::isAuthorized($publicActions['pages'], 'bar'));
        $this->assertTrue(Auth::isAuthorized($publicActions['Cms.sitemap'], 'foo'));
        $this->assertTrue(Auth::isAuthorized($publicActions['complex_1'], 'foo'));
        $this->assertFalse(Auth::isAuthorized($publicActions['complex_2'], 'foo'));
        $this->assertTrue(Auth::isAuthorized($publicActions['complex_3'], 'foobar'));
        $this->assertTrue(Auth::isAuthorized($publicActions['complex_4'], 'bar'));
        $this->assertTrue(Auth::isAuthorized($publicActions['complex_5'], 'bar'));
        $this->assertTrue(Auth::isAuthorized($publicActions['complex_6'], 'bar'));
        $this->assertFalse(Auth::isAuthorized($publicActions['complex_7'], 'bar'));
        $this->assertTrue(Auth::isAuthorized($publicActions['complex_8'], 'bar'));
        $this->assertTrue(Auth::isAuthorized($publicActions['complex_9'], 'bar'));
        $this->assertFalse(Auth::isAuthorized($publicActions['complex_10'], 'bar'));
    }

    public function testAuthForUserRights(): void
    {
        $userRights = [
            'viewAllUsers' => [
                'admin'
            ],
            'complex_1' => [
                '*'
            ],
            'complex_2' => [
                'foo',
                'bar'
            ],
            'complex_3' => [
                'role' => 'foo'
            ],
            'complex_4' => [
                'foo.bar.baz' => 'foo'
            ],
        ];

        $this->assertTrue(
            Auth::isAuthorized(
                $userRights,
                'viewAllUsers',
                [
                    'role' => 'admin'
                ]
            )
        );

        $this->assertFalse(
            Auth::isAuthorized(
                $userRights,
                'viewAllUsers',
                [
                    'role' => 'user'
                ]
            )
        );

        $this->assertTrue(
            Auth::isAuthorized(
                $userRights,
                'complex_1',
                [
                    'role' => 'user'
                ]
            )
        );

        $this->assertFalse(
            Auth::isAuthorized(
                $userRights,
                'complex_2',
                [
                    'role' => 'user'
                ]
            )
        );

        $this->assertTrue(
            Auth::isAuthorized(
                $userRights,
                'complex_3',
                [
                    'role' => 'foo'
                ]
            )
        );

        $this->assertTrue(
            Auth::isAuthorized(
                $userRights,
                'complex_4',
                [
                    'foo' => [
                        'bar' => [
                            'baz' => 'foo'
                        ]
                    ]
                ]
            )
        );
    }

    public function testAuthForAuthActions(): void
    {
        $authActions = [
            'Admin.dashbaord' => [
                '*' => ['admin'],
                'info' => ['*'],
                'my_info' => ['editor']
            ],
            'my_account' => [
                '*' => ['*']
            ],
            'complex_1' => [
                'foo' => [
                    'AND' => [
                        'role' => 'admin',
                        'age' => 12,
                        'gender.sex' => 'female'
                    ]
                ]
            ],
            'complex_2' => [
                'foo' => [
                    'OR' => [
                        'AND' => [
                            'role' => 'admin',
                            'age' => 12,
                            'gender.sex' => 'male'
                        ],
                        'gender.sex' => 'female'
                    ]
                ]
            ]
        ];

        $this->assertFalse(
            Auth::isAuthorized(
                $authActions['Admin.dashbaord'],
                'my_info',
                [
                    'role' => 'foo'
                ]
            )
        );

        $this->assertTrue(
            Auth::isAuthorized(
                $authActions['Admin.dashbaord'],
                'info',
                [
                    'role' => 'foo'
                ]
            )
        );

        $this->assertFalse(
            Auth::isAuthorized(
                $authActions['Admin.dashbaord'],
                'bar',
                [
                    'role' => 'foo'
                ]
            )
        );

        $this->assertTrue(
            Auth::isAuthorized(
                $authActions['Admin.dashbaord'],
                'my_info',
                [
                    'role' => 'editor'
                ]
            )
        );

        $this->assertTrue(
            Auth::isAuthorized(
                $authActions['my_account'],
                'my_info',
                [
                    'role' => 'editor'
                ]
            )
        );

        $this->assertTrue(
            Auth::isAuthorized(
                $authActions['complex_1'],
                'foo',
                [
                    'role' => 'admin',
                    'age' => 12,
                    'gender' => [
                        'sex' => 'female'
                    ]
                ]
            )
        );

        $this->assertFalse(
            Auth::isAuthorized(
                $authActions['complex_1'],
                'bar',
                [
                    'role' => 'admin',
                    'age' => 12,
                    'gender' => [
                        'sex' => 'female'
                    ]
                ]
            )
        );

        $this->assertTrue(
            Auth::isAuthorized(
                $authActions['complex_2'],
                'foo',
                [
                    'role' => 'admin',
                    'age' => 12,
                    'gender' => [
                        'sex' => 'female'
                    ]
                ]
            )
        );

        $this->assertFalse(
            Auth::isAuthorized(
                $authActions['complex_2'],
                'foo',
                [
                    'role' => 'admin',
                    'age' => 12,
                    'gender' => [
                        'sex' => 'apache attack helicopter'
                    ]
                ]
            )
        );
    }
}
