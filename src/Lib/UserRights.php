<?php
declare(strict_types = 1);
namespace AuthActions\Lib;

class UserRights
{
    /**
     * Holds the rights config.
     *
     * Format:
     *      'userCanDoStuff' => [
     *          role1, role2
     *      ],
     *      'userCanDoOtherStuff' => [
     *          role3
     *      ]
     *
     * @var array
     */
    protected $_rightsConfig = [];

    /**
     * Constructor
     *
     * @param array $rightsConfig The rights configuration
     */
    public function __construct(array $rightsConfig)
    {
        $this->_rightsConfig = $rightsConfig;
    }

    /**
     * Checks if the given user has a right
     *
     * @param array $user user to check
     * @param string $right right name
     * @return bool
     */
    public function userHasRight(array $user, string $right): bool
    {
        $hasRight = false;
        if (isset($user['role']) && !empty($right) && isset($this->_rightsConfig[$right])) {
            if (in_array($user['role'], $this->_rightsConfig[$right])) {
                $hasRight = true;
            }
        }

        return $hasRight;
    }
}
