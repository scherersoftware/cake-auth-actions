<?php
declare(strict_types=1);

namespace AuthActions\Lib;

use Cake\Utility\Hash;

class Auth
{
    protected const AND = 'AND';
    protected const OR = 'OR';

    /**
     * @param array  $user
     * @param array  $config
     * @param string $name
     * @return bool
     */
    public static function userHasPermissionFor(array $user, array $config, string $name): bool
    {
        if (array_key_exists('*', $config)) {
            $name = '*';
        }

        if (!array_key_exists($name, $config) && in_array($name, $config)) {
            return true;
        }

        if (!array_key_exists($name, $config) && !in_array($name, $config)) {
            return false;
        }

        if (!is_array($config[$name])) {
            return false;
        }

        return self::userHasPermission($user, $config[$name]);
    }

    /**
     * @param array $user
     * @param array $config
     * @return bool
     */
    public static function userHasPermission(array $user, array $config): bool
    {
        $resolver = function($key, $value) use ($user) {
            return self::_resolveValue($user, $key, $value);
        };

        return self::_resolve($config, $resolver, self:: OR);
    }

    public static function globalHasPermission(array $config, string $permission): bool
    {
        if (array_key_exists('*', $config)) {
            $permission = '*';
        }

        if (!array_key_exists($permission, $config) && in_array($permission, $config)) {
            return true;
        }

        if (!array_key_exists($permission, $config) && !in_array($permission, $config)) {
            return false;
        }

        $resolver = function($key, $value) {
            if (is_callable($value)) {
                $value = $value($key);
            }

            if (is_array($value)) {
                return in_array($key, $value);
            }

            return $value === true;
        };

        if (!is_array($config[$permission])) {
            return $resolver($permission, $config[$permission]);
        }

        return self::_resolve( $config[$permission], $resolver, self:: OR);
    }

    /**
     * @param array  $user
     * @param array  $config
     * @param string $type
     * @return bool
     */
    protected static function _resolve(array $config, callable $resolver, string $type = 'OR'): bool
    {
        if (isset($config['*'])) {
            return self::_resolve($config['*'], $resolver, self:: OR);
        }

        $return = false;

        foreach ($config as $key => $value) {
            $result = false;

            if (is_bool($value)) {
                $result = $value;
            } elseif (is_string($key)) {
                if (is_array($value)) {
                    if ($key === self:: AND) {
                        $result = self::_resolve($value, $resolver, self:: AND);
                    } elseif ($key === self:: OR) {
                        $result = self::_resolve($value, $resolver, self:: OR);
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


            if ($result === true && $type === self:: OR) {
                return true;
            }

            if ($result === false && $type === self:: AND) {
                return false;
            }

            if ($result === true && $type === self:: AND) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * @param array  $user
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    protected static function _resolveValue(array $user, string $key, $value): bool
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
