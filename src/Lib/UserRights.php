<?php
declare(strict_types = 1);
namespace AuthActions\Lib;

class UserRights
{

    /**
     * Holds the rights config.
     * Format:
     *        'userCanDoStuff' => array(
     *            role1, role2
     *        ),
     *        'userCanDoOtherStuff' => array(
     *            role3
     *        )
     *
     * @var string
     */
    protected $_rightsConfig = [];

    /**
     * Constructor
     *
     * @param array $rightsConfig The rights configuration
     */
    public function __construct(array $rightsConfig = [])
    {
        $this->_rightsConfig = $rightsConfig;
    }

    /**
     * Checks if the given user has a right
     *
     * @param array  $user  user to check
     * @param string $right right name
     * @return bool
     */
    public function userHasRight(array $user, string $right): bool
    {
        return Auth::isAuthorized($this->_rightsConfig, $right, $user);
    }
}
