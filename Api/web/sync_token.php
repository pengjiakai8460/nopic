<?php
/**
 * Created by PhpStorm.
 * User: chenhc
 * Date: 2018/3/29
 * Time: 上午11:50
 */
$token = $_GET['token_key'];
$_COOKIE['token_key'] = $token;
setcookie('token_key', $token, null, '/');