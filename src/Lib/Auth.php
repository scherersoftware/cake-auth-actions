<?php
declare(strict_types = 1);
namespace AuthActions\Lib;

use Cake\Utility\Hash;

class Auth
{
    protected const OPERATOR_AND = 'AND';
    protected const OPERATOR_OR = 'OR';

    /**
     * Returns whether the permission is authorized with the given data.
     *
     * @param array      $config          Config
     * @param string     $permission      Permission
     * @param array|null $user            User
     * @param string     $defaultOperator Default comparision operator
     * @return bool
     */
    public static function isAuthorized(
        array $config,
        string $permission,
        array $user = null,
        string $defaultOperator = self::OPERATOR_OR
    ): bool {
        if (!array_key_exists($permission, $config)) {
            if (array_key_exists('*', $config)) {
                $permission = '*';
            } elseif (!in_array($permission, $config) && in_array('*', $config)) {
                return true;
            }
        }

        if (!array_key_exists($permission, $config) && in_array($permission, $config)) {
            return true;
        }

        if (!array_key_exists($permission, $config) && !in_array($permission, $config)) {
            return false;
        }

        $resolver = [__CLASS__, '_resolveValueForGlobal'];
        if (is_array($user)) {
            $resolver = function ($key, $value) use ($user) {
                return self::_resolveValueForUser($user, $key, $value);
            };
        }

        if (is_array($config[$permission])) {
            return self::_resolve($config[$permission], $resolver, $defaultOperator);
        }

        return $resolver($permission, $config[$permission]);
    }

    /**
     * Function that recursively goes through a configuration array and calls the value resolver if necessary.
     *
     * @param array    $config   Configuration
     * @param callable $resolver Resolver function
     * @param string   $type     Type
     * @return bool
     */
    protected static function _resolve(array $config, callable $resolver, string $type = 'OR'): bool
    {
        if (isset($config['*'])) {
            return self::_resolve($config['*'], $resolver, self:: OPERATOR_OR);
        }

        $return = false;

        foreach ($config as $key => $value) {
            $result = false;

            if (is_bool($value)) {
                $result = $value;
            } elseif (is_string($key)) {
                if (is_array($value)) {
                    if ($key === self::OPERATOR_AND) {
                        $result = self::_resolve($value, $resolver, self::OPERATOR_AND);
                    } elseif ($key === self::OPERATOR_OR) {
                        $result = self::_resolve($value, $resolver, self::OPERATOR_OR);
                    } else {
                        $result = $resolver($key, $value);
                    }
                } elseif ($value === '*') {
                    $result = true;
                } else {
                    $result = $resolver($key, $value);
                }
            } else {
                if ($value === '*') {
                    $result = true;
                } else {
                    $result = $resolver('role', $value);
                }
            }

            if ($result === true && $type === self:: OPERATOR_OR) {
                return true;
            }

            if ($result === false && $type === self:: OPERATOR_AND) {
                return false;
            }

            if ($result === true && $type === self:: OPERATOR_AND) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Resolves a value.
     *
     * @param string $key   Array key
     * @param mixed  $value Array value
     * @return bool
     */
    protected static function _resolveValueForGlobal(string $key, $value): bool
    {
        if (is_callable($value)) {
            $value = $value($key);
        }

        if (is_array($value)) {
            return in_array($key, $value);
        }

        return $value === true;
    }

    /**
     * Resolves a value for the given user.
     *
     * @param array  $user  User
     * @param string $key   Array key
     * @param mixed  $value Array value
     * @return bool
     */
    protected static function _resolveValueForUser(array $user, string $key, $value): bool
    {
        $userValue = Hash::get($user, $key);

        if (is_callable($value)) {
            $value = $value($key, $userValue, $user);
        }

        if (is_array($value)) {
            return in_array($userValue, $value);
        }

        return $userValue === $value;
    }
}
