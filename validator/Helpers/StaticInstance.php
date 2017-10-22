<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/22
 * Time: 10:29
 */

namespace Zsxsoft\AppValidator\Helpers;


trait StaticInstance
{

    private static $_instance = NULL;

    protected static function _initializeThis () {
        self::$_instance = new self();
    }

    public static function __callStatic($name, $arguments) {
        if (is_null(self::$_instance)) {
            self::_initializeThis();
        }
        return call_user_func_array([self::$_instance, $name], $arguments);
    }

    public static function instance () {
        return self::$_instance;
    }
}