<?php 


/**
 * This is the basic singleton pattern called "Pocket" as something
 * that you use to put something else into and keep it there.
 * 
 * Basically, stores anything server-related as a cache and global
 * storage for latter use.
 */
final class Pocket {
    private static array $objects = [];


    /**
     * Returns persistent object, stored in singleton. Singleton
     * instance get via lazy initialization (created on first usage).
     * Once stored persistent object cannot be modified later.
     */
    public static function __callStatic($name, $args)
    {
        if (empty(static::$objects[$name])) {

            if (count($args) === 0) {
                static::$objects[$name] = null;
            } else if (count($args) === 1) {
                static::$objects[$name] = $args[0];
            } else {
                static::$objects[$name] = $args;
            }
        }

        return static::$objects[$name];
    }

    /**
     * It's not allowed to call from outside to prevent from
     * creating multiple instances, to use the singleton, you have
     * to obtain the instance from `getInstance()` method instead.
     */
    private function __construct(){}

    /**
     * Prevent the instance from being cloned,
     * which would create a second instance of it.
     */
    private function __clone(){}

    /**
     * Prevent from being unserialized, which
     * would create a second instance of it.
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
