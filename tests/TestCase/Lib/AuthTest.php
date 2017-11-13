<?php
declare(strict_types = 1);

namespace AuthActions\Test\TestCase\Lib;

use AuthActions\Lib\Auth;
use Cake\TestSuite\TestCase;

class AuthTest extends TestCase
{
    public function testGlobalHasPermission()
    {
        $enabled = Auth::isAuthorized(
            [
                'bar' => [
                    function () {
                        return false;
                    }
                ]
            ],
            'bar'
        );

        $this->assertFalse($enabled);

        $enabled = Auth::isAuthorized(
            [
                'bar' => [
                    function () {
                        return true;
                    }
                ]
            ],
            'bar'
        );

        $this->assertTrue($enabled);

        $enabled = Auth::isAuthorized(
            [
                'bar' => true
            ],
            'bar'
        );

        $this->assertTrue($enabled);

        $enabled = Auth::isAuthorized(
            [
                'bar'
            ],
            'barz'
        );

        $this->assertFalse($enabled);

        $enabled = Auth::isAuthorized(
            [
                'bar'
            ],
            'bar'
        );

        $this->assertTrue($enabled);
    }

    public function testUserHasPermissionFor()
    {
        $enabled = Auth::userIsAuthorized(
            [
                'role' => 'foobar',
                'status' => 'barbar'
            ],
            [
                'foo' => [
                    '*'
                ]
            ],
            'bar'
        );

        $this->assertFalse($enabled);

        $enabled = Auth::userIsAuthorized(
            [
                'role' => 'foobar',
                'status' => 'barbar'
            ],
            [
                'foo' => [
                    'role' => 'asdf'
                ]
            ],
            'foo'
        );

        $this->assertFalse($enabled);

        $enabled = Auth::userIsAuthorized(
            [
                'role' => 'foobar',
                'status' => 'barbar'
            ],
            [
                'foo' => [
                    '*'
                ]
            ],
            'foo'
        );

        $this->assertTrue($enabled);
    }

    public function testUserHasPermission()
    {
        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            [
                'foobar'
            ]
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'auth' => [
                'foo' => [
                    'bar' => [
                        'baz' => false
                    ]
                ]
            ]
        ], [
            'auth.foo.bar.baz' => function ($value) {
                return false;
            }
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'auth' => [
                'foo' => [
                    'bar' => [
                        'baz' => true
                    ]
                ]
            ]
        ], [
            'auth.foo.bar.baz' => function ($value) {
                return true;
            }
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'auth' => [
                'foo' => [
                    'bar' => [
                        'baz' => 'noot'
                    ]
                ]
            ]
        ], [
            'auth.foo.bar.baz' => function ($value) {
                return 'noot';
            }
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar',
            'auth' => [
                'foo' => 'bar'
            ]
        ], [
            'AND' => [
                'role' => 'foobar',
                'auth.foo' => [
                    'bar',
                    'baz'
                ]
            ]
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar',
            'auth' => [
                'foo' => 'bazz'
            ]
        ], [
            'role' => function () {
                return 'foobar';
            }
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar',
            'auth' => [
                'foo' => 'bazz'
            ]
        ], [
            true
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar',
            'auth' => [
                'foo' => 'bazz'
            ]
        ], [
            false
        ]);

        $this->assertFalse($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar',
            'auth' => [
                'foo' => 'bazz'
            ]
        ], [
            'AND' => [
                'role' => 'foobar',
                'auth.foo' => [
                    'bar',
                    'baz'
                ]
            ]
        ]);

        $this->assertFalse($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'abc',
            'def',
            'foobar2'
        ]);

        $this->assertFalse($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'abc',
            'def',
            'foobar'
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'AND' => [
                'role' => ['*']
            ]
        ]);

        $this->assertFalse($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'AND' => [
                'AND' => [
                    'OR' => [
                        'AND' => [
                            'AND' => [
                                'OR' => [
                                    'AND' => [
                                        'role' => 'foobar',
                                        'status' => 'barbar'
                                    ],
                                    'status' => 'boop'
                                ]
                            ]
                        ]
                    ],
                    'role' => 'foobar'
                ]
            ]
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'AND' => [
                'AND' => [
                    'OR' => [
                        'AND' => [
                            'AND' => [
                                'OR' => [
                                    'AND' => [
                                        'role' => 'foobar',
                                        'status' => 'barbar'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'OR' => [
                'role' => '*',
                'status' => 'barbarbar'
            ]
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'AND' => [
                'role' => '*',
                'status' => 'barbarbar'
            ]
        ]);

        $this->assertFalse($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'AND' => [
                'role' => '*',
                'status' => 'barbar'
            ]
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'role' => '*',
            'status' => 'barbar'
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'role' => '*'
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            '*' => [
                'foobar',
                'barbar'
            ]
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'fooba2r',
            'status' => 'barbar'
        ], [
            '*' => [
                ['foobar', 'barbar']
            ]
        ]);

        $this->assertFalse($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            '*' => [
                'role' => ['foobar', 'barbar']
            ]
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            '*'
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foobar',
            'status' => 'barbar'
        ], [
            'AND' => [
                'role' => 'foo',
                'status' => 'bar',
            ],
            'status' => 'barbar'
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'status' => 'active'
        ], [
            'status' => 'active'
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'status' => 'inactive'
        ], [
            'status' => 'active'
        ]);

        $this->assertFalse($enabled);

        $enabled = Auth::userHasPermission([
            'status' => 'active',
            'role' => 'foo'
        ], [
            'status' => 'active',
            'role' => 'foo'
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foo'
        ], [
            'status' => 'active',
            'role' => 'foo'
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foo'
        ], [
            'status' => 'active',
            'role' => 'bar'
        ]);

        $this->assertFalse($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foo',
            'status' => 'bar'
        ], [
            'AND' => [
                'role' => 'foo',
                'status' => 'bar',
            ]
        ]);

        $this->assertTrue($enabled);

        $enabled = Auth::userHasPermission([
            'role' => 'foo',
            'status' => 'foobar'
        ], [
            'AND' => [
                'role' => 'foo',
                'status' => 'bar',
            ]
        ]);

        $this->assertFalse($enabled);
    }
}
