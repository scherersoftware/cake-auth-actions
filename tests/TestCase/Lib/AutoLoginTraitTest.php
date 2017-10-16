<?php

namespace AuthActions\Test\TestCase\Lib;

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * @property \Cake\ORM\Table UsersTable
 */
class AutoLoginTraitTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.auth_actions.users'
    ];


    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->UsersTable = TableRegistry::get('Users');
        $this->UsersTable->setEntityClass('AuthActions\Test\TestModel\Entity\User');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->UsersTable);
        TableRegistry::clear();
        parent::tearDown();
    }

    public function testUserLoginToken()
    {
        $userId = 1;
        $userToTest = $this->UsersTable->get($userId);

        $url = [
            'controller' => 'Test',
            'action' => 'test'
        ];

        $mockDatetime = Time::now()->modify('-2 days');
        Time::setTestNow($mockDatetime);
        $validToken = $userToTest->generateLoginToken($url, '2 weeks');
        $expiredToken = $userToTest->generateLoginToken($url);
        $invalidToken = strrev($validToken);
        Time::setTestNow(null);

        $validValidationResult = $userToTest->validateLoginToken($validToken);
        $expiredValidationResult = $userToTest->validateLoginToken($expiredToken);
        $inValidationResult = $userToTest->validateLoginToken($invalidToken);

        $this->assertSame($validValidationResult['url'], $url);
        $this->assertNull($expiredValidationResult);
        $this->assertNull($inValidationResult);
    }

}
