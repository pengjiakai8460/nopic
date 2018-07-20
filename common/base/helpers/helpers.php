<?php

use yii\helpers\StringHelper;

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return null;
        }

        if (StringHelper::startsWith($value, '"') && StringHelper::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (! function_exists('env2dsn')) {

    function env2dsn($hostKey, $portKey, $nameKey)
    {
        $host = env($hostKey, 'localhost');
        $port = env($portKey, '3306');
        $dbname = env($nameKey, 'forget');


        if ($host && $port && $dbname) {
            if ($dbname == "testxmjy") {
                //print_r(sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $dbname));
                //exit;
            }

            return sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);
        }

        return '';
    }
}

if (! function_exists('str_hide')) {

    function str_hide($str)
    {
        if (strlen($str) >= 11) {
            $str = substr_replace($str, '****', 3, (strlen($str)-7));
        } elseif (strlen($str) > 4) {
            $str = substr_replace($str, '****', 1, (strlen($str)-2));
        } else {
            $str = '****';
        }

        return $str;
    }
}

if (! function_exists('idcard_regex')) {

    function idcard_regex()
    {
        return '/^\d{17}[\dXx]|\d{15}$/i';
    }
}

if (! function_exists('account_regex')) {

    function account_regex()
    {
        return '/(^1\d{10,}$)|(^[a-z0-9!#$%&\'*+\/=?^_`{|}~.-]+@[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*$)/i';
    }
}

if (! function_exists('password_regex')) {

    function password_regex()
    {
        return '/^[!@#$%^&*? a-zA-Z\d]/';
//        return '/^.*(?=.{6,})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*? ]).*$/';
    }
}