<?php

namespace AuthActions\Test\TestModel\Entity;

use AuthActions\Lib\AutoLoginTrait;
use Cake\ORM\Entity;

class User extends Entity
{
    use AutoLoginTrait;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
    ];
}