<?php
namespace Course\services;

use common\base\BaseService;

abstract class ServiceAbstract extends BaseService
{
    protected static $_models = array();

    abstract public static function model();
}